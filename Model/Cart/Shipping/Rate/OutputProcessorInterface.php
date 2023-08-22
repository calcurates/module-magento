<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Cart\Shipping\Rate;

interface OutputProcessorInterface
{
    /**
     * @param array $data
     * @param string $stringToProcess
     * @return string
     */
    public function process(array $data, string $stringToProcess): string;
}
