<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Model\Order;

use Calcurates\ModuleMagento\Api\SalesData\OrderData\OrderAddressExtensionAttributesInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\OrderAddressData\CollectionFactory;
use Magento\Sales\Api\Data\OrderAddressSearchResultInterface;
use Magento\Sales\Api\Data\OrderInterface;

class AddResidentialExtensionAttribute
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * AddResidentialExtensionAttribute constructor.
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param OrderInterface $order
     * @param OrderAddressSearchResultInterface $collection
     * @return OrderAddressSearchResultInterface
     */
    public function afterGetAddressesCollection(OrderInterface $order, OrderAddressSearchResultInterface $collection)
    {
        if ($collection->getItems()) {
            foreach ($collection->getItems() as $address) {
                if ($address->getAddressType() === 'shipping') {
                    $extensionAttributes = $address->getExtensionAttributes();
                    if (!$extensionAttributes->getResidentialDelivery()) {
                        $orderAddressExtension = $this->collectionFactory
                            ->create()
                            ->addFieldToFilter(
                                OrderAddressExtensionAttributesInterface::MAGENTO_ORDER_ADDRESS_ID,
                                $address->getEntityId()
                            )->getFirstItem();
                        if ($orderAddressExtension->getData()) {
                            $extensionAttributes->setResidentialDelivery($orderAddressExtension);
                            $address->setExtensionAttributes($extensionAttributes);
                        }
                    }
                }
            }
        }
        return $collection;
    }
}
