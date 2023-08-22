<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\InfoMessage;

use Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\OutputProcessorInterface;

class TaxAmount implements OutputProcessorInterface
{
    /**
     * @var string
     */
    private $variableString = '{tax_amount}';

    /**
     * @var array
     */
    private $requiredFieldsToProcess = [
        'tax_amount',
        'currency_code'
    ];

    /**
     * @param array $data
     * @param string $stringToProcess
     * @return string
     */
    public function process(array $data, string $stringToProcess): string
    {
        if (\strpos($stringToProcess, $this->variableString) !== false) {
            if ($data['tax_amount'] && !array_diff($this->requiredFieldsToProcess, array_keys($data))) {
                $stringToProcess = str_replace(
                    $this->variableString,
                    $data['tax_amount'].' '.$data['currency_code'],
                    $stringToProcess
                );
            }
        }
        return $stringToProcess;
    }
}
