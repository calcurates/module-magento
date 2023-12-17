<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\InfoMessage;

use Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\OutputProcessorInterface;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Framework\DataObject;

class Tags implements OutputProcessorInterface
{
    /**
     * @var array
     */
    protected $variablesTemplate = [
        "\n" => '<br />',
    ];

    /**
     * @param Rate|Method|Error|DataObject $rateModel
     * @param string $stringToProcess
     * @return string
     */
    public function process(DataObject $rateModel, string $stringToProcess): string
    {
        $stringToProcess = strip_tags($stringToProcess);
        foreach ($this->variablesTemplate as $variableTemplateValue => $variableReplace) {
            $stringToProcess = str_replace($variableTemplateValue, $variableReplace, $stringToProcess);
        }
        return $stringToProcess;
    }
}
