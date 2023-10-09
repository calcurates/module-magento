<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Cart\Shipping\Rate;

use Magento\Quote\Model\Quote\Address\Rate;

interface OutputProcessorInterface
{
    /**
     * @param Rate $rateModel
     * @param string $stringToProcess
     * @return string
     */
    public function process(Rate $rateModel, string $stringToProcess): string;
}
