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
use Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\InfoMessage\Packages;
use Magento\Framework\DataObject;

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
     * @var Packages
     */
    private $packageMessageProcessor;

    /**
     * TableRateProcessor constructor.
     * @param FailedRateBuilder $failedRateBuilder
     * @param RateBuilder $rateBuilder
     * @param TableRateNameBuilder $tableRateNameBuilder
     * @param Config $configProvider
     * @param Packages $packageMessageProcessor
     */
    public function __construct(
        FailedRateBuilder $failedRateBuilder,
        RateBuilder $rateBuilder,
        TableRateNameBuilder $tableRateNameBuilder,
        Config $configProvider,
        Packages $packageMessageProcessor
    ) {
        $this->packageMessageProcessor = $packageMessageProcessor;
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
            if (!$tableRate['success'] && empty($tableRate['methods'])) {
                if ($tableRate['message']) {
                    $failedRate = $this->failedRateBuilder->build(
                        $tableRate['displayName'] ?? $tableRate['name'],
                        '',
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
                            $tableRate['displayName'] ?? $tableRate['name'],
                            $rateName,
                            $responseRateMethod['message'],
                            $tableRate['priority']
                        );
                        $result->append($failedRate);
                    }

                    continue;
                }

                $responseRateMethod['priority'] = $tableRate['priority'] + $responseRateMethod['priority'] * 0.001;
                $responseRateMethod['imageUri'] = $responseRateMethod['imageUri'] ?: $tableRate['imageUri'];
                $responseRateMethod['name'] = $this->tableRateNameBuilder->buildName(
                    $responseRateMethod,
                    $this->configProvider->isDisplayPackageNameForCarrier()
                );

                unset($responseRateMethod['displayName'], $responseRateMethod['additionalText']);
                if (isset($responseRateMethod['message']) && $responseRateMethod['message']) {
                    $responseRateMethod['message'] = $this->packageMessageProcessor
                        ->process(new DataObject($responseRateMethod), $responseRateMethod['message']);
                }
                $rates = $this->rateBuilder->build(
                    ShippingMethodManager::TABLE_RATE . '_' . $tableRate['id'] . '_' . $responseRateMethod['id'],
                    $responseRateMethod,
                    $tableRate['displayName'] ?? $tableRate['name']
                );

                foreach ($rates as $rate) {
                    $result->append($rate);
                }
            }
        }
    }
}
