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
     * CarrierProcessor constructor.
     * @param FailedRateBuilder $failedRateBuilder
     * @param RateBuilder $rateBuilder
     * @param SerializerInterface $serializer
     */
    public function __construct(
        FailedRateBuilder $failedRateBuilder,
        RateBuilder $rateBuilder,
        SerializerInterface $serializer
    ) {
        $this->failedRateBuilder = $failedRateBuilder;
        $this->rateBuilder = $rateBuilder;
        $this->serializer = $serializer;
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

        $carrierServicesToOrigins = [];
        foreach ($response['shippingOptions']['carriers'] as $carrier) {
            if (!$isHaveRates($carrier)) {
                if ($carrier['message']) {
                    $failedRate = $this->failedRateBuilder->build(
                        $carrier['name'],
                        $carrier['message']
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
                            $responseCarrierRate['message']
                        );
                        $result->append($failedRate);
                    }

                    continue;
                }

                $serviceIds = [];
                $serviceNames = [];
                $sourceToServiceId = [];
                $message = [];
                foreach ($responseCarrierRate['services'] as $service) {
                    $name = $serviceNames[$service['name']] ?? $service['name'] . ' - ';
                    if (isset($service['package']['name'])) {
                        $name .= $service['package']['name'];
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

                $methodId = $this->getUniqueMethodId(
                    ShippingMethodManager::CARRIER . '_' . $carrier['id'] . '_' . $serviceIdsString,
                    $existingMethodIds
                );

                $existingMethodIds[$methodId] = true;

                $responseCarrierRate['priority'] = $carrier['priority'];
                $rate = $this->rateBuilder->build(
                    $methodId,
                    $responseCarrierRate,
                    $carrier['name']
                );

                $rate->setData(RatesResponseProcessor::CALCURATES_TOOLTIP_MESSAGE, implode(' ', $message));
                $result->append($rate);
            }
        }

        $quote->setData(CustomSalesAttributesInterface::CARRIER_SOURCE_CODE_TO_SERVICE, $this->serializer->serialize($carrierServicesToOrigins));
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
