<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Config\Source;

class DeliveryDateDisplayTypeSource implements \Magento\Framework\Data\OptionSourceInterface
{
    public const DAYS_QTY = 'days_qty';
    public const DATES = 'dates';
    public const DATES_MAGENTO_FORMAT = 'dates_magento_format';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Qty of days in transit'), 'value' => self::DAYS_QTY],
            ['label' => __('Delivery dates - Magento format'), 'value' => self::DATES_MAGENTO_FORMAT],
            ['label' => __('Custom Date Format'), 'value' => self::DATES],
        ];
    }
}
