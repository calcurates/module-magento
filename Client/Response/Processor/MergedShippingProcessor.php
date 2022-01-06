<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor;

use Calcurates\ModuleMagento\Client\RateBuilder;
use Calcurates\ModuleMagento\Client\Response\FailedRateBuilder;
use Calcurates\ModuleMagento\Client\Response\ResponseProcessorInterface;
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Shipping\Model\Rate\Result;
use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Magento\Framework\Serialize\SerializerInterface;

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
     * MergedShippingProcessor constructor.
     * @param FailedRateBuilder $failedRateBuilder
     * @param RateBuilder $rateBuilder
     * @param SerializerInterface $serializer
     */
    public function __construct(
        FailedRateBuilder $failedRateBuilder,
        RateBuilder $rateBuilder,
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
        $this->failedRateBuilder = $failedRateBuilder;
        $this->rateBuilder = $rateBuilder;
    }

    /**
     * @param Result $result
     * @param array $response
     * @param CartInterface $quote
     */
    public function process(Result $result, array $response, CartInterface $quote): void
    {
        foreach ($response['shippingOptions']['mergedShippingOptions'] as $responseRate) {
            if (!$responseRate['success']) {
                if ($responseRate['message']) {
                    $failedRate = $this->failedRateBuilder->build(
                        $responseRate['name'],
                        $responseRate['message'],
                        $responseRate['priority']
                    );
                    $result->append($failedRate);
                }
                continue;
            }
            $carriePrefix = '';
            if (array_key_exists('carriers', $responseRate) && $responseRate['carriers']) {
                $carriePrefix = 'carrier_';
                $carrierIds = [];
                foreach ($responseRate['carriers'] as $carrier) {
                    $carrierIds[] = $carrier['id'];
                }
                $carriePrefix .= implode(',', $carrierIds);
            }
            $rates = $this->rateBuilder->build(
                ShippingMethodManager::MERGRED_SHIPPING . '_' . $carriePrefix . '_' . $responseRate['id'],
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
                    $message = [];
                    $packages = [];
                    foreach ($responseCarrierRate['services'] as $service) {
                        foreach ($service['packages'] ?? [] as $package) {
                            $packages[] = $package;
                        }

                        if (!empty($service['message'])) {
                            $message[] = $service['message'];
                        }

                        $serviceIds[] = $service['id'];

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
