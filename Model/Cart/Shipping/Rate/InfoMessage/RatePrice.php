<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\InfoMessage;

use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\OutputProcessorInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\Method;

class RatePrice implements OutputProcessorInterface
{
    /**
     * @var array
     */
    private $variablesTemplate = [
        RatesResponseProcessor::CALCURATES_FINAL_RATE => '{final_rate}',
        RatesResponseProcessor::CALCURATES_BASE_RATE => '{base_rate}',
        RatesResponseProcessor::CALCURATES_SURCHARGE => '{surcharge}',
        RatesResponseProcessor::CALCURATES_DISCOUNT => '{discount}',
    ];

    /**
     * @var array
     */
    private $ruleVariablesTemplate = [
        RatesResponseProcessor::CALCURATES_SURCHARGE_RULES => '{surcharge_rule',
        RatesResponseProcessor::CALCURATES_DISCOUNT_RULES => '{discount_rule',
    ];

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(PriceCurrencyInterface $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param Rate|Method|Error|DataObject $rateModel
     * @param string $stringToProcess
     * @return string
     */
    public function process(DataObject $rateModel, string $stringToProcess): string
    {
        $currency = $rateModel->getData(RatesResponseProcessor::CALCURATES_CURRENCY);
        foreach ($this->variablesTemplate as $dataKey => $variableTemplate) {
            if (false === strpos($stringToProcess, $variableTemplate)) {
                continue;
            }
            $amount = $rateModel->getData($dataKey);
            if (!isset($amount, $currency)) {
                continue;
            }
            $stringToProcess = str_replace(
                $variableTemplate,
                $this->formatAmount($amount, $currency),
                $stringToProcess
            );
        }

        foreach ($this->ruleVariablesTemplate as $dataKey => $placeholderPrefix) {
            if (false === strpos($stringToProcess, $placeholderPrefix)) {
                continue;
            }
            $rules = $rateModel->getData($dataKey);
            if (!is_array($rules) || !isset($currency)) {
                continue;
            }
            foreach ($rules as $ruleId => $amount) {
                $placeholder = $placeholderPrefix . $ruleId . '}';
                if (!isset($amount) || false === strpos($stringToProcess, $placeholder)) {
                    continue;
                }
                $stringToProcess = str_replace(
                    $placeholder,
                    $this->formatAmount($amount, $currency),
                    $stringToProcess
                );
            }
        }

        return $stringToProcess;
    }

    /**
     * @param float|int|string $amount
     * @param string $currency
     * @return string
     */
    private function formatAmount($amount, string $currency): string
    {
        return $this->priceCurrency->convertAndFormat(
            $amount,
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            null,
            $currency
        );
    }
}
