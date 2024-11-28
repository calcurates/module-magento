<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Config\Source;

class InfoMessageDisplayPositionSource implements \Magento\Framework\Data\OptionSourceInterface
{
    public const IN_TOOLTIP = 'in_tooltip';
    public const BELOW_METHOD = 'below_method';

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Show in the tooltip'), 'value' => self::IN_TOOLTIP],
            ['label' => __('Show below shipping method'), 'value' => self::BELOW_METHOD],
        ];
    }
}
