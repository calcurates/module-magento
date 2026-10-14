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
use Magento\Framework\Escaper;
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
        RatesResponseProcessor::CALCURATES_INSURANCE_AMOUNT => '{insurance_amount}',
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
     * @var Escaper
     */
    private $escaper;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param Escaper $escaper
     */
    public function __construct(PriceCurrencyInterface $priceCurrency, Escaper $escaper)
    {
        $this->priceCurrency = $priceCurrency;
        $this->escaper = $escaper;
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
     * Labeled amounts for the checkout shipping method. Empty when the rate has nothing to show.
     *
     * @param Rate|Method|Error|DataObject $rateModel
     * @return string
     */
    public function buildDisplay(DataObject $rateModel): string
    {
        $currency = $rateModel->getData(RatesResponseProcessor::CALCURATES_CURRENCY);
        if (!isset($currency)) {
            return '';
        }

        $lines = [];
        $amountLabels = [
            RatesResponseProcessor::CALCURATES_FINAL_RATE => __('Final rate'),
            RatesResponseProcessor::CALCURATES_BASE_RATE => __('Base rate'),
            RatesResponseProcessor::CALCURATES_SURCHARGE => __('Surcharge'),
            RatesResponseProcessor::CALCURATES_DISCOUNT => __('Discount'),
        ];
        foreach ($amountLabels as $dataKey => $label) {
            $amount = $rateModel->getData($dataKey);
            if (!isset($amount)) {
                continue;
            }
            $lines[] = $this->renderLine((string)$label, $this->formatAmount($amount, $currency));
        }

        $ruleLabels = [
            RatesResponseProcessor::CALCURATES_SURCHARGE_RULES => 'Surcharge rule %1',
            RatesResponseProcessor::CALCURATES_DISCOUNT_RULES => 'Discount rule %1',
        ];
        foreach ($ruleLabels as $dataKey => $label) {
            $rules = $rateModel->getData($dataKey);
            if (!is_array($rules)) {
                continue;
            }
            foreach ($rules as $ruleId => $amount) {
                if (!isset($amount)) {
                    continue;
                }
                $lines[] = $this->renderLine(
                    (string)__($label, $ruleId),
                    $this->formatAmount($amount, $currency)
                );
            }
        }

        return implode('', $lines);
    }

    /**
     * @param string $label
     * @param string $amount
     * @return string
     */
    private function renderLine(string $label, string $amount): string
    {
        return '<div>' . $this->escaper->escapeHtml($label) . ': '
            . $this->escaper->escapeHtml($amount) . '</div>';
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
