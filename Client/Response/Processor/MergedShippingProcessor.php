<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Calcurates\ModuleMagento\Client\RateBuilder;
use Calcurates\ModuleMagento\Client\Response\FailedRateBuilder;
use Calcurates\ModuleMagento\Client\Response\ResponseProcessorInterface;
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Shipping\Model\Rate\Result;

class MergedShippingProcessor implements ResponseProcessorInterface
{
    /**
     * @var FailedRateBuilder
     */
    private $failedRateBuilder;

    /**
     * @var RateBuilder
     */
    private $rateBuilder;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var State
     */
    private $appState;

    /**
     * MergedShippingProcessor constructor.
     * @param FailedRateBuilder $failedRateBuilder
     * @param RateBuilder $rateBuilder
     * @param SerializerInterface $serializer
     * @param State $appState
     */
    public function __construct(
        FailedRateBuilder $failedRateBuilder,
        RateBuilder $rateBuilder,
        SerializerInterface $serializer,
        State $appState
    ) {
        $this->serializer = $serializer;
        $this->failedRateBuilder = $failedRateBuilder;
        $this->rateBuilder = $rateBuilder;
        $this->appState = $appState;
    }

    /**
     * @param array $responseRate
     * @return array
     */
    private function makeErrorMessages(array $responseRate): array
    {
        $messages = [];
        if (array_key_exists('message', $responseRate) && $responseRate['message']) {
            $messages = $responseRate['message'];
        }
        foreach ($responseRate['flatRates'] as $flatRate) {
            if ($flatRate['message']) {
                $messages[] = $flatRate['message'];
            }
        }
        foreach ($responseRate['freeShipping'] as $freeShipping) {
            if ($freeShipping['message']) {
                $messages[] = $freeShipping['message'];
            }
        }
        foreach ($responseRate['tableRates'] as $tableRate) {
            foreach ($tableRate['methods'] as $method) {
                if ($method['message']) {
                    $messages[] = $method['message'];
                }
            }
        }
        foreach ($responseRate['inStorePickups'] as $inStorePickup) {
            foreach ($inStorePickup['stores'] as $store) {
                if ($store['message']) {
                    $messages[] = $store['message'];
                }
            }
        }
        foreach ($responseRate['carriers'] as $carrier) {
            foreach ($carrier['rates'] ?? [] as $carrierRate) {
                if ($carrierRate['message']) {
                    $messages[] = $carrierRate['message'];
                }
            }
        }
        foreach ($responseRate['rateShopping'] as $rateShopping) {
            foreach ($rateShopping['carriers'] ?? [] as $rateShoppingCarrier) {
                foreach ($rateShoppingCarrier['rates'] ?? [] as $rateShoppingCarrierRate) {
                    if ($rateShoppingCarrierRate['message']) {
                        $messages[] = $rateShoppingCarrierRate['message'];
                    }
                }
            }
        }

        return $messages;
    }

    /**
     * @param Result $result
     * @param array $response
     * @param CartInterface $quote
     * @return void
     */
    public function process(Result $result, array &$response, CartInterface $quote): void
    {
        foreach ($response['shippingOptions']['mergedShippingOptions'] as $responseRate) {
            if ($this->appState->getAreaCode() === Area::AREA_ADMINHTML) {
                $responseRate['displayName'] = $responseRate['name']
                                               . (!empty($responseRate['displayName']) ? " ({$responseRate['displayName']})" : '');
            } else {
                $responseRate['displayName'] = $responseRate['displayName'] ?? $responseRate['name'];
            }

            if (!$responseRate['success']) {
                $messages = $this->makeErrorMessages($responseRate);
                if ($messages) {
                    $failedRate = $this->failedRateBuilder->build(
                        '',
                        $responseRate['displayName'],
                        implode("\n", \array_unique($messages)),
                        $responseRate['priority']
                    );
                    $result->append($failedRate);
                }
                continue;
            }

            $carriePrefix = '';
            if ($responseRate['carriers']) {
                $carriePrefix = 'carrier_';
                $carrierIds = [];
                foreach ($responseRate['carriers'] as $carrier) {
                    $carrierIds[] = $carrier['id'];
                }
                $carriePrefix .= implode(',', $carrierIds);
            }
            $rates = $this->rateBuilder->build(
                ShippingMethodManager::MERGED_SHIPPING . '_' . $carriePrefix . '_' . $responseRate['id'],
                $responseRate,
                ''
            );

            foreach ($rates as $rate) {
                $result->append($rate);
            }
            $carrierServicesToOrigins = $carrierRatesToPackages = [];
            foreach ($responseRate['carriers'] as $carrier) {
                foreach ($carrier['rates'] ?? [] as $responseCarrierRate) {
                    $serviceIds = [];
                    $sourceToServiceId = [];
                    $packages = [];
                    foreach ($responseCarrierRate['services'] as $service) {
                        $serviceIds[] = $service['id'];

                        foreach ($service['packages'] ?? [] as $package) {
                            $packages[] = $package;
                        }

                        $sourceCode = $service['origin']['syncedTargetOriginCode'] ?? null;
                        if ($sourceCode) {
                            $sourceToServiceId[$sourceCode] = $service['id'];
                        }
                    }
                    $serviceIdsString = implode(',', $serviceIds);
                    $carrierServicesToOrigins[$carrier['id']][$serviceIdsString] = $sourceToServiceId;
                    $carrierRatesToPackages[$carrier['id']][$serviceIdsString] = $packages;
                }
            }

            $existingCarrierServicesToOrigins = $quote->getData(
                CustomSalesAttributesInterface::CARRIER_SOURCE_CODE_TO_SERVICE
            ) ?: [];
            if ($existingCarrierServicesToOrigins) {
                $existingCarrierServicesToOrigins = $this->serializer->unserialize($existingCarrierServicesToOrigins);
                foreach ($existingCarrierServicesToOrigins as $carrierId => $serviceIdData) {
                    foreach ($serviceIdData as $serviceIds => $source) {
                        $mergedSource = $source;
                        if (isset($carrierServicesToOrigins[$carrierId][$serviceIds])) {
                            $mergedSource = array_merge($source, $carrierServicesToOrigins[$carrierId][$serviceIds]);
                        }
                        $carrierServicesToOrigins[$carrierId][$serviceIds] = $mergedSource;
                    }
                }
            }
            $quote->setData(
                CustomSalesAttributesInterface::CARRIER_SOURCE_CODE_TO_SERVICE,
                $this->serializer->serialize($carrierServicesToOrigins)
            );

            $existingCarrierRatesToPackages = $quote->getData(
                CustomSalesAttributesInterface::CARRIER_PACKAGES
            ) ?: [];
            if ($existingCarrierRatesToPackages) {
                $existingCarrierRatesToPackages = $this->serializer->unserialize($existingCarrierRatesToPackages);
                foreach ($existingCarrierRatesToPackages as $carrierId => $serviceIdData) {
                    foreach ($serviceIdData as $serviceIds => $source) {
                        $mergedSource = $source;
                        if (isset($carrierRatesToPackages[$carrierId][$serviceIds])) {
                            $mergedSource = array_merge($source, $carrierRatesToPackages[$carrierId][$serviceIds]);
                        }
                        $carrierRatesToPackages[$carrierId][$serviceIds] = $mergedSource;
                    }
                }
            }
            $quote->setData(
                CustomSalesAttributesInterface::CARRIER_PACKAGES,
                $this->serializer->serialize($carrierRatesToPackages)
            );
        }
    }
}
