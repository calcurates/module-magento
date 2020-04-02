<?php

namespace Calcurates\ModuleMagento\Plugin\Model\Quote;

use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Sales\Api\Data\OrderItemInterface;

class ToOrderItemPlugin
{
    /**
     * @param ToOrderItem $subject
     * @param OrderItemInterface $result
     * @param Item|AddressItem $item
     * @param array $data
     * @return OrderItemInterface
     */
    public function afterConvert(ToOrderItem $subject, $result, $item, $data = [])
    {
        $result->setData('calcurates_source_code', $item->getData('calcurates_source_code'));

        return $result;
    }
}
