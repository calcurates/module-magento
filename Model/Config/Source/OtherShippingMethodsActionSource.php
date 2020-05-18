<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Config\Source;

class OtherShippingMethodsActionSource implements \Magento\Framework\Data\OptionSourceInterface
{
    const ALWAYS_HIDE = 1;
    const SHOW_IF_ERROR_OR_EXCEEDS_TIMEOUT = 2;
    const ALWAYS_SHOW = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ALWAYS_HIDE, 'label' => __('Always hide')],
            ['value' => self::SHOW_IF_ERROR_OR_EXCEEDS_TIMEOUT, 'label' => __('Show if error or exceeds API timeout')],
            ['value' => self::ALWAYS_SHOW, 'label' => __('Always show')],
        ];
    }
}
