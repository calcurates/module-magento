<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor\SplitCheckout;

use Calcurates\ModuleMagento\Client\RateBuilder;
use Calcurates\ModuleMagento\Client\Response\FailedRateBuilder;
use Calcurates\ModuleMagento\Client\Response\Processor\Utils\CarrierRateNameBuilder;
use Calcurates\ModuleMagento\Client\Response\Processor\Utils\ChildChecker;
use Calcurates\ModuleMagento\Client\Response\Processor\Utils\StringUniqueIncrement;
use Calcurates\ModuleMagento\Client\Response\ResponseProcessorInterface;
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
use Calcurates\ModuleMagento\Model\Config;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Shipping\Model\Rate\Result;

class RateShoppingProcessor implements ResponseProcessorInterface
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
     * @var ChildChecker
     */
    private $childChecker;

    /**
     * @var CarrierRateNameBuilder
     */
    private $carrierRateNameBuilder;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var StringUniqueIncrement
     */
    private $stringUniqueIncrement;

    /**
     * @param FailedRateBuilder $failedRateBuilder
     * @param RateBuilder $rateBuilder
     * @param ChildChecker $childChecker
     * @param CarrierRateNameBuilder $carrierRateNameBuilder
     * @param Config $configProvider
     * @param StringUniqueIncrement $stringUniqueIncrement
     */
    public function __construct(
        FailedRateBuilder $failedRateBuilder,
        RateBuilder $rateBuilder,
        ChildChecker $childChecker,
        CarrierRateNameBuilder $carrierRateNameBuilder,
        Config $configProvider,
        StringUniqueIncrement $stringUniqueIncrement
    ) {
        $this->failedRateBuilder = $failedRateBuilder;
        $this->rateBuilder = $rateBuilder;
        $this->childChecker = $childChecker;
        $this->carrierRateNameBuilder = $carrierRateNameBuilder;
        $this->configProvider = $configProvider;
        $this->stringUniqueIncrement = $stringUniqueIncrement;
    }

    /**
     * @param Result $result
     * @param array $response
     * @param CartInterface $quote
     */
    public function process(Result $result, array &$response, CartInterface $quote): void
    {
        foreach ($response['shippingOptions']['rateShopping'] as $rateShopping) {
            if (!$this->childChecker->isHaveRates($rateShopping, 'carriers')) {
                if ($rateShopping['message']) {
                    $failedRate = $this->failedRateBuilder->build(
                        $rateShopping['name'],
                        $rateShopping['message'],
                        $rateShopping['priority']
                    );
                    $result->append($failedRate);
                }

                continue;
            }

            foreach ($rateShopping['carriers'] as $carrier) {
                if (!$carrier['success']) {
                    if ($carrier['message']) {
                        $failedRate = $this->failedRateBuilder->build(
                            $carrier['name'],
                            $carrier['message'],
                            $rateShopping['priority']
                        );
                        $result->append($failedRate);
                    }

                    continue;
                }

                $existingMethodIds = [];
                foreach ($carrier['rates'] ?? [] as $rate) {
                    $services = [];
                    if (!$rate['success']) {
                        if ($rate['message']) {
                            $services['services'][] = $rate['service'];
                            $rateName = $this->carrierRateNameBuilder->buildName(
                                $services,
                                $this->configProvider->isDisplayPackageNameForCarrier()
                            );

                            $failedRate = $this->failedRateBuilder->build(
                                $rateName,
                                $rate['message'],
                                $rateShopping['priority']
                            );
                            $result->append($failedRate);
                        }

                        continue;
                    }

                    $servicesPriority = 0;
                    $serviceIds = $messages = [];
                    foreach ($rate['services'] ?? [] as $service) {
                        if (!empty($service['message'])) {
                            $messages[] = $service['message'];
                        }

                        $serviceIds[] = $service['id'];
                        if (!empty($service['priority'])) {
                            $servicesPriority += $service['priority'] * 0.001;
                        }
                    }

                    if (isset($rate['service']['id'])) {
                        $serviceIds[] = $rate['service']['id'];
                        $rate = array_merge($rate, $rate['service']);
                    }

                    $serviceIdsString = implode(',', $serviceIds);

                    $services['services'][] = $rate['service'];
                    $rate['name'] = $this->carrierRateNameBuilder->buildName(
                        $services,
                        $this->configProvider->isDisplayPackageNameForCarrier()
                    );

                    $methodId = $this->stringUniqueIncrement->getUniqueString(
                        ShippingMethodManager::RATE_SHOPPING . '_' .
                            $rateShopping['id'] . '_' . $carrier['id'] . '_' . $serviceIdsString,
                        $existingMethodIds
                    );

                    $existingMethodIds[$methodId] = true;

                    $rate['priority'] = $rateShopping['priority'] + $rate['service']['priority'] * 0.001;
                    $rate['imageUri'] = $rateShopping['imageUri'];
                    $rate['message'] = implode(' ', $messages);

                    unset($rate['displayName'], $rate['additionalText']);
                    $rates = $this->rateBuilder->build(
                        $methodId,
                        $rate,
                        $carrier['name']
                    );

                    foreach ($rates as $rateItem) {
                        $result->append($rateItem);
                    }
                }
            }
        }
    }
}
