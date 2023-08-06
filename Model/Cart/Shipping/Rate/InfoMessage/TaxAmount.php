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
        'price_including_tax',
        'price',
        'currency_code'
    ];

    /**
     * @param array $data
     * @param string $stringToProcess
     * @return string
     */
    public function process(array $data, string $stringToProcess): string
    {
        if (substr_count($stringToProcess, $this->variableString)) {
            if (!array_diff($this->requiredFieldsToProcess, array_keys($data))) {
                $stringToProcess = str_replace(
                    $this->variableString,
                    number_format((float)($data['price_including_tax'] - $data['price']), 2)
                        . $data['currency_code'],
                    $stringToProcess
                );
            }
        }
        return $stringToProcess;
    }
}
