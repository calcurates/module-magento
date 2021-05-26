<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Config\Source;

class DisplayInStorePickupAsSource implements \Magento\Framework\Data\OptionSourceInterface
{
    public const SHIPPING_METHODS = 'shipping_methods';
    public const STORES_SELECTOR = 'stores_selector';

    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::SHIPPING_METHODS, 'label' => __('Shipping Methods')],
            ['value' => self::STORES_SELECTOR, 'label' => __('Stores Selector')],
        ];
    }
}
