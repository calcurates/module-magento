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
use Magento\Quote\Model\Quote\Address\Rate;

class TaxAmount implements OutputProcessorInterface
{
    /**
     * @var string
     */
    private $variableTemplate = '{tax_amount}';

    /**
     * @param Rate $rateModel
     * @param string $stringToProcess
     * @return string
     */
    public function process(Rate $rateModel, string $stringToProcess): string
    {
        if (false === \strpos($stringToProcess, $this->variableTemplate)) {
            return $stringToProcess;
        }

        $taxAmount = $rateModel->getData(RatesResponseProcessor::CALCURATES_TAX_AMOUNT);
        $currency = $rateModel->getData(RatesResponseProcessor::CALCURATES_CURRENCY);
        if (!isset($taxAmount, $currency)) {
            return $stringToProcess;
        }

        return str_replace(
            $this->variableTemplate,
            $taxAmount.' '.$currency,
            $stringToProcess
        );
    }
}
