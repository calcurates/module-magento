<?php

namespace Calcurates\ModuleMagento\Plugin\Model\Quote\Totals;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Quote\Api\Data\TotalsItemInterface;
use Magento\Quote\Api\Data\TotalsItemInterfaceFactory;
use Magento\Quote\Model\Cart\Totals\ItemConverter;
use Magento\Quote\Model\Quote\Item;

class QuoteItemConverter
{
    /**
     * @var DataObjectHelper
     */
    private DataObjectHelper $dataObjectHelper;

    /**
     * @var TotalsItemInterfaceFactory
     */
    private TotalsItemInterfaceFactory $totalsItemFactory;

    /**
     * @param DataObjectHelper $dataObjectHelper
     * @param TotalsItemInterfaceFactory $totalsItemFactory
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        TotalsItemInterfaceFactory $totalsItemFactory
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->totalsItemFactory = $totalsItemFactory;
    }

    /**
     * Add bundle product children (shipped separately) to extension attributes
     *
     * @param ItemConverter $subject
     * @param TotalsItemInterface $result
     * @param Item $item
     * @return TotalsItemInterface
     */
    public function afterModelToDataObject(
        ItemConverter $subject,
        TotalsItemInterface $result,
        Item $item
    ): TotalsItemInterface {
        if ($item->getHasChildren() && $item->isShipSeparately()) {
            $children = [];

            foreach ($item->getChildren() as $child) {
                $itemsData = $this->totalsItemFactory->create();
                $item = $child->toArray();
                $item['options'] = "[]";
                $this->dataObjectHelper->populateWithArray(
                    $itemsData,
                    $item,
                    TotalsItemInterface::class
                );
                $children[] = $itemsData;
            }
            $extensionAttributes = $result->getExtensionAttributes();
            $extensionAttributes->setBundleChildren($children);
            $result->setExtensionAttributes($extensionAttributes);
        }
        return $result;
    }
}
