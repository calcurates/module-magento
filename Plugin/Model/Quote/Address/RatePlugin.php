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
        $tooltip = $rate->getData(RatesResponseProcessor::CALCURATES_TOOLTIP_MESSAGE);
        if ($tooltip) {
            $result->setData(RatesResponseProcessor::CALCURATES_TOOLTIP_MESSAGE, $tooltip);
        }

        return $result;
    }
}
