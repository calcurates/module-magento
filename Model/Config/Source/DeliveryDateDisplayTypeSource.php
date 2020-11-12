<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Config\Source;

class DeliveryDateDisplayTypeSource implements \Magento\Framework\Data\OptionSourceInterface
{
    const DAYS_QTY = 'days_qty';
    const DATES = 'dates';
    const DATES_MAGENTO_FORMAT = 'dates_magento_format';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Qty of days in transit'), 'value' => self::DAYS_QTY],
            ['label' => __('Estimated delivery dates'), 'value' => self::DATES],
            ['label' => __('Estimated delivery dates (Magento format)'), 'value' => self::DATES_MAGENTO_FORMAT],
        ];
    }
}
