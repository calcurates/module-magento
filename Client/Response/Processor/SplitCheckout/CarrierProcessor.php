<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor\SplitCheckout;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Calcurates\ModuleMagento\Client\RateBuilder;
use Calcurates\ModuleMagento\Client\Response\FailedRateBuilder;
use Calcurates\ModuleMagento\Client\Response\Processor\Utils\CarrierRateNameBuilder;
use Calcurates\ModuleMagento\Client\Response\Processor\Utils\ChildChecker;
use Calcurates\ModuleMagento\Client\Response\Processor\Utils\StringUniqueIncrement;
use Calcurates\ModuleMagento\Client\Response\ResponseProcessorInterface;
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
use Calcurates\ModuleMagento\Model\Config;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Shipping\Model\Rate\Result;

class CarrierProcessor implements ResponseProcessorInterface
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
     * @var Config
     */
    private $configProvider;

    /**
     * @var ChildChecker
     */
    private $childChecker;

    /**
     * @var CarrierRateNameBuilder
     */
    private $carrierRateNameBuilder;

    /**
     * @var StringUniqueIncrement
     */
    private $stringUniqueIncrement;

    /**
     * CarrierProcessor constructor.
     * @param FailedRateBuilder $failedRateBuilder
     * @param RateBuilder $rateBuilder
     * @param SerializerInterface $serializer
     * @param Config $configProvider
     * @param ChildChecker $childChecker
     * @param CarrierRateNameBuilder $carrierRateNameBuilder
     * @param StringUniqueIncrement $stringUniqueIncrement
     */
    public function __construct(
        FailedRateBuilder $failedRateBuilder,
        RateBuilder $rateBuilder,
        SerializerInterface $serializer,
        Config $configProvider,
        ChildChecker $childChecker,
        CarrierRateNameBuilder $carrierRateNameBuilder,
        StringUniqueIncrement $stringUniqueIncrement
    ) {
        $this->failedRateBuilder = $failedRateBuilder;
        $this->rateBuilder = $rateBuilder;
        $this->serializer = $serializer;
        $this->configProvider = $configProvider;
        $this->childChecker = $childChecker;
        $this->carrierRateNameBuilder = $carrierRateNameBuilder;
        $this->stringUniqueIncrement = $stringUniqueIncrement;
    }

    /**
     * @TODO refactor that
     * @param Result $result
     * @param array $response
     * @param CartInterface $quote
     */
    public function process(Result $result, array &$response, CartInterface $quote): void
    {
        $carrierServicesToOrigins = $carrierRatesToPackages = [];
        foreach ($response['shippingOptions']['carriers'] as $carrier) {
            if (!$this->childChecker->isHaveRates($carrier, 'rates')) {
                if ($carrier['message']) {
                    $failedRate = $this->failedRateBuilder->build(
                        $carrier['name'],
                        '',
                        $carrier['message'],
                        $carrier['priority']
                    );
                    $result->append($failedRate);
                }

                continue;
            }

            $existingMethodIds = [];
            $existingServiceIds = [];
            foreach ($carrier['rates'] ?? [] as $responseCarrierRate) {
                $services = [];
                if (!$responseCarrierRate['success']) {
                    if ($responseCarrierRate['message']) {
                        $services['services'][] = $responseCarrierRate['service'];
                        $rateName = $this->carrierRateNameBuilder->buildName(
                            $services,
                            $this->configProvider->isDisplayPackageNameForCarrier()
                        );

                        $failedRate = $this->failedRateBuilder->build(
                            $carrier['displayName'] ?? $carrier['name'],
                            $rateName,
                            $responseCarrierRate['message'],
                            $carrier['priority']
                        );
                        $result->append($failedRate);
                    }

                    continue;
                }

                $serviceIds = [];
                $sourceToServiceId = [];
                $packages = [];

                foreach ($responseCarrierRate['service']['packages'] ?? [] as $package) {
                    $package['origin_id'] = $response['origin']['id'];
                    $packages[] = $package;
                }

                $serviceIds[] = $responseCarrierRate['service']['id'];

                $sourceCode = $responseCarrierRate['service']['origin']['syncedTargetOriginCode'] ?? null;

                if ($sourceCode) {
                    $sourceToServiceId[$sourceCode] = $responseCarrierRate['service']['id'];
                }

                $services['services'][] = $responseCarrierRate['service'];
                $responseCarrierRate['name'] = $this->carrierRateNameBuilder->buildName(
                    $services,
                    $this->configProvider->isDisplayPackageNameForCarrier()
                );

                $serviceIdsString = $this->stringUniqueIncrement->getUniqueString(
                    implode(',', $serviceIds),
                    $existingServiceIds
                );

                $existingServiceIds[$serviceIdsString] = true;

                $carrierServicesToOrigins[$carrier['id']][$serviceIdsString] = $sourceToServiceId;
                $carrierRatesToPackages[$carrier['id']][$serviceIdsString] = $packages;

                $methodId = $this->stringUniqueIncrement->getUniqueString(
                    ShippingMethodManager::CARRIER . '_' . $carrier['id'] . '_' . $serviceIdsString,
                    $existingMethodIds
                );

                $existingMethodIds[$methodId] = true;

                $responseCarrierRate['priority'] =
                    $carrier['priority'] + $responseCarrierRate['service']['priority'] * 0.001;
                $responseCarrierRate['imageUri'] = $carrier['imageUri'];
                $responseCarrierRate['message'] = $responseCarrierRate['message'] ?? $responseCarrierRate['service']['message'];
                $responseCarrierRate['rate'] = $responseCarrierRate['service']['rate'];
                $rates = $this->rateBuilder->build(
                    $methodId,
                    $responseCarrierRate,
                    $carrier['displayName'] ?? $carrier['name']
                );

                foreach ($rates as $rate) {
                    $result->append($rate);
                }
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
                        $mergedSource = array_unique(
                            array_merge($source, $carrierRatesToPackages[$carrierId][$serviceIds]),
                            SORT_REGULAR
                        );
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
