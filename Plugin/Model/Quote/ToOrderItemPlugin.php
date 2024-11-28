<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Model\Quote;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
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
        $quoteItem = $item->getQuoteItem() ? $item->getQuoteItem() : $item;
        $result->setData(
            CustomSalesAttributesInterface::SOURCE_CODE,
            $quoteItem->getData(CustomSalesAttributesInterface::SOURCE_CODE)
        );

        return $result;
    }
}
