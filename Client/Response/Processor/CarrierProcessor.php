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
                        $carrier['displayName'] ?? $carrier['name'],
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
                if (!$responseCarrierRate['success']) {
                    if ($responseCarrierRate['message']) {
                        $rateName = $this->carrierRateNameBuilder->buildName(
                            $responseCarrierRate,
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
                $messages = [];
                $packages = [];
                $servicesPriority = 0;
                foreach ($responseCarrierRate['services'] as $service) {
                    $sourceCode = $service['origin']['syncedTargetOriginCode'] ?? null;
                    foreach ($service['packages'] ?? [] as $package) {
                        $packages[] = array_merge($package, ['source_code' => $sourceCode]);
                    }

                    if (!empty($service['message'])) {
                        $messages[] = $service['message'];
                    }

                    if (!empty($service['priority'])) {
                        $servicesPriority += $service['priority'] * 0.001;
                    }

                    $serviceIds[] = $service['id'];
                    if ($sourceCode) {
                        $sourceToServiceId[$sourceCode] = $service['id'];
                    }
                }

                $responseCarrierRate['name'] = $this->carrierRateNameBuilder->buildName(
                    $responseCarrierRate,
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

                $responseCarrierRate['priority'] = $carrier['priority'] + $servicesPriority;
                $responseCarrierRate['imageUri'] = $carrier['imageUri'];
                $responseCarrierRate['message'] = $responseCarrierRate['message'] ?? implode(' ', \array_unique($messages));
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
            try {
                $existingCarrierServicesToOrigins = $this->serializer->unserialize($existingCarrierServicesToOrigins);
            } catch (\Exception $e) {
                $existingCarrierServicesToOrigins = [];
            }
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
            try {
                $existingCarrierRatesToPackages = $this->serializer->unserialize($existingCarrierRatesToPackages);
            } catch (\Exception $e) {
                $existingCarrierRatesToPackages = [];
            }
            foreach ($existingCarrierRatesToPackages as $carrierId => $serviceIdData) {
                foreach ($serviceIdData as $serviceIds => $source) {
                    if (isset($carrierRatesToPackages[$carrierId][$serviceIds])) {
                        $mergedSource = $carrierRatesToPackages[$carrierId][$serviceIds];
                        foreach ($source as $packageToMerge) {
                            if (!in_array($packageToMerge, $mergedSource)) {
                                $mergedSource[] = $packageToMerge;
                            }
                        }
                        $carrierRatesToPackages[$carrierId][$serviceIds] = $mergedSource;
                    }
                }
            }
        }
        $quote->setData(
            CustomSalesAttributesInterface::CARRIER_PACKAGES,
            $this->serializer->serialize($carrierRatesToPackages)
        );
    }
}
