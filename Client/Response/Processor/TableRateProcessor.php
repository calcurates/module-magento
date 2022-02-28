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
use Calcurates\ModuleMagento\Client\Response\Processor\Utils\TableRateNameBuilder;
use Calcurates\ModuleMagento\Client\Response\ResponseProcessorInterface;
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
use Calcurates\ModuleMagento\Model\Config;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Shipping\Model\Rate\Result;

class TableRateProcessor implements ResponseProcessorInterface
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
     * @var TableRateNameBuilder
     */
    private $tableRateNameBuilder;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * TableRateProcessor constructor.
     * @param FailedRateBuilder $failedRateBuilder
     * @param RateBuilder $rateBuilder
     * @param TableRateNameBuilder $tableRateNameBuilder
     * @param Config $configProvider
     */
    public function __construct(
        FailedRateBuilder $failedRateBuilder,
        RateBuilder $rateBuilder,
        TableRateNameBuilder $tableRateNameBuilder,
        Config $configProvider
    ) {
        $this->failedRateBuilder = $failedRateBuilder;
        $this->rateBuilder = $rateBuilder;
        $this->tableRateNameBuilder = $tableRateNameBuilder;
        $this->configProvider = $configProvider;
    }

    /**
     * @param Result $result
     * @param array $response
     * @param CartInterface $quote
     */
    public function process(Result $result, array &$response, CartInterface $quote): void
    {
        foreach ($response['shippingOptions']['tableRates'] as $tableRate) {
            if (!$tableRate['success']) {
                if ($tableRate['message']) {
                    $failedRate = $this->failedRateBuilder->build(
                        $tableRate['name'],
                        $tableRate['message'],
                        $tableRate['priority']
                    );
                    $result->append($failedRate);
                }

                continue;
            }

            foreach ($tableRate['methods'] as $responseRateMethod) {
                if (!$responseRateMethod['success']) {
                    if ($responseRateMethod['message']) {
                        $rateName = $this->tableRateNameBuilder->buildName(
                            $responseRateMethod,
                            $this->configProvider->isDisplayPackageNameForCarrier()
                        );
                        $failedRate = $this->failedRateBuilder->build(
                            $rateName,
                            $responseRateMethod['message'],
                            $tableRate['priority']
                        );
                        $result->append($failedRate);
                    }

                    continue;
                }

                $responseRateMethod['priority'] = $tableRate['priority'];
                $responseRateMethod['imageUri'] = $responseRateMethod['imageUri'] ?: $tableRate['imageUri'];
                $responseRateMethod['name'] = $this->tableRateNameBuilder->buildName(
                    $responseRateMethod,
                    $this->configProvider->isDisplayPackageNameForCarrier()
                );
                $rates = $this->rateBuilder->build(
                    ShippingMethodManager::TABLE_RATE . '_' . $tableRate['id'] . '_' . $responseRateMethod['id'],
                    $responseRateMethod,
                    $tableRate['name']
                );

                foreach ($rates as $rate) {
                    $result->append($rate);
                }
            }
        }
    }
}
