<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\Model\Quote\Address;

use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Magento\Quote\Model\Quote\Address\Rate;

class RatePlugin
{

    /**
     * @param Rate $subject
     * @param Rate $result
     * @param \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult $rate
     * @return Rate
     */
    public function afterImportShippingRate(
        Rate $subject,
        $result,
        \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult $rate
    ) {
        $arrayToImport = [
            RatesResponseProcessor::CALCURATES_TOOLTIP_MESSAGE,
            RatesResponseProcessor::CALCURATES_DELIVERY_DATES,
        ];
        foreach ($arrayToImport as $key) {
            $value = $rate->getData($key);
            if ($value !== null) {
                $result->setData($key, $value);
            }
        }

        return $result;
    }
}
