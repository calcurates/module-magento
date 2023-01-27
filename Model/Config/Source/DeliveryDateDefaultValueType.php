<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Config\Source;

class DeliveryDateDefaultValueType implements \Magento\Framework\Data\OptionSourceInterface
{
    public const EARLIEST = 'earliest';
    public const EARLIEST_CHEAPEST = 'earliest_cheapest';

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Earliest'), 'value' => self::EARLIEST],
            ['label' => __('Earliest Cheapest'), 'value' => self::EARLIEST_CHEAPEST],
        ];
    }
}
