<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Config\Source;

class DeliveryDateDisplaySource implements \Magento\Framework\Data\OptionSourceInterface
{
    const DO_NOT_SHOW = 'do_not_show';
    const TOOLTIP = 'tooltip';
    const AFTER_METHOD_NAME = 'after_method_name';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Do not show'), 'value' => self::DO_NOT_SHOW],
            ['label' => __('Show in the tooltip'), 'value' => self::TOOLTIP],
            ['label' => __('Show next to the method name'), 'value' => self::AFTER_METHOD_NAME],
        ];
    }
}
