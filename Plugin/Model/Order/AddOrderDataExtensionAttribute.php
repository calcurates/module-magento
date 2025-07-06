<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Model\Order;

use Calcurates\ModuleMagento\Api\SalesData\OrderData\GetOrderDataInterface;
use Magento\Sales\Api\Data\OrderAddressExtensionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;

class AddOrderDataExtensionAttribute
{
    /**
     * @var GetOrderDataInterface
     */
    private $getOrderData;

    /**
     * @var OrderAddressExtensionFactory
     */
    private $addressExtensionFactory;

    /**
     * AddOrderDataExtensionAttribute constructor.
     * @param GetOrderDataInterface $getOrderData
     * @param OrderAddressExtensionFactory $addressExtensionFactory
     */
    public function __construct(
        GetOrderDataInterface $getOrderData,
        OrderAddressExtensionFactory $addressExtensionFactory
    ) {
        $this->addressExtensionFactory = $addressExtensionFactory;
        $this->getOrderData = $getOrderData;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        $extensionAttributes = $order->getExtensionAttributes();
        if (!$extensionAttributes || !$extensionAttributes->getShippingAssignments()) {
            return $order;
        }
        foreach ($extensionAttributes->getShippingAssignments() as $assignment) {
            $shipping = $assignment->getShipping();
            $address = $shipping->getAddress();
            if ($address) {
                $addressExtensionAttributes = $address->getExtensionAttributes()
                    ?: $this->addressExtensionFactory->create();
                $orderData = $this->getOrderData->get($order->getEntityId());
                if ($orderData) {
                    $addressExtensionAttributes->setCalcuratesDeliveryDates($orderData);
                }
                $address->setExtensionAttributes($addressExtensionAttributes);
            }
        }
        return $order;
    }
}
