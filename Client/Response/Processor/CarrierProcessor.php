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
use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Calcurates\ModuleMagento\Client\Response\FailedRateBuilder;
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
     * CarrierProcessor constructor.
     * @param FailedRateBuilder $failedRateBuilder
     * @param RateBuilder $rateBuilder
     * @param SerializerInterface $serializer
     * @param Config $configProvider
     */
    public function __construct(
        FailedRateBuilder $failedRateBuilder,
        RateBuilder $rateBuilder,
        SerializerInterface $serializer,
        Config $configProvider
    ) {
        $this->failedRateBuilder = $failedRateBuilder;
        $this->rateBuilder = $rateBuilder;
        $this->serializer = $serializer;
        $this->configProvider = $configProvider;
    }

    /**
     * @TODO refactor that
     * @param Result $result
     * @param array $response
     * @param CartInterface $quote
     */
    public function process(Result $result, array $response, CartInterface $quote): void
    {
        $isHaveRates = static function (array $carrier) {
            if ($carrier['success']) {
                return true;
            }
            if ($carrier['rates']) {
                foreach ($carrier['rates'] as $rate) {
                    if ($rate['message']) {
                        return true;
                    }
                }
            }

            return false;
        };

        $carrierServicesToOrigins = $carrierRatesToPackages = [];
        foreach ($response['shippingOptions']['carriers'] as $carrier) {
            if (!$isHaveRates($carrier)) {
                if ($carrier['message']) {
                    $failedRate = $this->failedRateBuilder->build(
                        $carrier['name'],
                        $carrier['message'],
                        $carrier['priority']
                    );
                    $result->append($failedRate);
                }

                continue;
            }

            $existingMethodIds = [];
            foreach ($carrier['rates'] as $responseCarrierRate) {
                if (!$responseCarrierRate['success']) {
                    if ($responseCarrierRate['message']) {
                        $serviceNames = [];
                        $rateServices = $responseCarrierRate['services'] ?? [];
                        foreach ($rateServices as $rateService) {
                            $serviceNames[] =  $rateService['name'];
                        }
                        $rateName = implode(' ', $serviceNames);


                        $failedRate = $this->failedRateBuilder->build(
                            $rateName,
                            $responseCarrierRate['message'],
                            $carrier['priority']
                        );
                        $result->append($failedRate);
                    }

                    continue;
                }

                $serviceIds = [];
                $serviceNames = [];
                $sourceToServiceId = [];
                $message = [];
                $packages = [];
                foreach ($responseCarrierRate['services'] as $service) {
                    $name = $serviceNames[$service['name']] ?? $service['name'] . ' - ';
                    $packageNames = [];
                    foreach ($service['packages'] ?? [] as $package) {
                        $packageNames[] = $package['name'];
                        $packages[] = $package;
                    }
                    if ($this->configProvider->isDisplayPackageNameForCarrier()) {
                        $name .= implode(';', $packageNames);
                    }

                    if (!empty($service['message'])) {
                        $message[] = $service['message'];
                    }

                    $serviceNames[$service['name']] = $name;
                    $serviceIds[] = $service['id'];

                    $sourceCode = $service['origin']['targetValue']['targetId'] ?? null;

                    if ($sourceCode) {
                        $sourceToServiceId[$sourceCode] = $service['id'];
                    }
                }

                $serviceIdsString = implode(',', $serviceIds);
                $responseCarrierRate['name'] = implode(', ', array_map(static function ($serviceName) {
                    return rtrim($serviceName, ' - ');
                }, $serviceNames));

                $carrierServicesToOrigins[$carrier['id']][$serviceIdsString] = $sourceToServiceId;
                $carrierRatesToPackages[$carrier['id']][$serviceIdsString] = $packages;

                $methodId = $this->getUniqueMethodId(
                    ShippingMethodManager::CARRIER . '_' . $carrier['id'] . '_' . $serviceIdsString,
                    $existingMethodIds
                );

                $existingMethodIds[$methodId] = true;

                $responseCarrierRate['priority'] = $carrier['priority'];
                $responseCarrierRate['imageUri'] = $carrier['imageUri'];
                $responseCarrierRate['message'] = implode(' ', $message);
                $rates = $this->rateBuilder->build(
                    $methodId,
                    $responseCarrierRate,
                    $carrier['name']
                );

                foreach ($rates as $rate) {
                    $result->append($rate);
                }

            }
        }

        $quote->setData(CustomSalesAttributesInterface::CARRIER_SOURCE_CODE_TO_SERVICE, $this->serializer->serialize($carrierServicesToOrigins));
        $quote->setData(CustomSalesAttributesInterface::CARRIER_PACKAGES, $this->serializer->serialize($carrierRatesToPackages));
    }

    /**
     * @param string $baseMethodId
     * @param array $listExistingMethods
     * @return string
     */
    private function getUniqueMethodId(string $baseMethodId, array $listExistingMethods): string
    {
        $i = 1;
        $methodId = $baseMethodId;
        while (isset($listExistingMethods[$methodId])) {
            $methodId = $baseMethodId . '_' . $i;
            $i++;
        }

        return $methodId;
    }
}
