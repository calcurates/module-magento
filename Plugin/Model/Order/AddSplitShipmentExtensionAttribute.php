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
use Calcurates\ModuleMagento\Api\Data\Order\SplitShipmentInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Calcurates\ModuleMagento\Api\Data\Order\SplitShipment\ProductQtyInterfaceFactory;
use Calcurates\ModuleMagento\Api\Data\Order\SplitShipment\ProductQtyInterface;

class AddSplitShipmentExtensionAttribute
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
     * @var SplitShipmentInterfaceFactory
     */
    private $splitShipmentFactory;

    /**
     * @var ProductQtyInterfaceFactory
     */
    private $productQtyFactory;

    /**
     * AddSplitShipmentExtensionAttribute constructor.
     * @param GetOrderDataInterface $getOrderData
     * @param SplitShipmentInterfaceFactory $splitShipmentFactory
     * @param ProductQtyInterfaceFactory $productQtyInterfaceFactory
     * @param OrderAddressExtensionFactory $addressExtensionFactory
     */
    public function __construct(
        GetOrderDataInterface $getOrderData,
        SplitShipmentInterfaceFactory $splitShipmentFactory,
        ProductQtyInterfaceFactory $productQtyInterfaceFactory,
        OrderAddressExtensionFactory $addressExtensionFactory
    ) {
        $this->addressExtensionFactory = $addressExtensionFactory;
        $this->productQtyFactory = $productQtyInterfaceFactory;
        $this->getOrderData = $getOrderData;
        $this->splitShipmentFactory = $splitShipmentFactory;
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
                if ($orderData && $orderData->getSplitShipments()) {
                    $splitShipments = [];
                    foreach ($orderData->getSplitShipments() as $splitShipment) {
                        $productQty = [];
                        foreach ($splitShipment['product_qty'] as $sku => $qty) {
                            $productQty[] = [
                                ProductQtyInterface::QTY => $qty,
                                ProductQtyInterface::SKU => $sku,
                            ];
                        }
                        $splitShipment['product_qty'] = $productQty;
                        $splitShipments[] = $this->splitShipmentFactory->create(['data' => $splitShipment]);
                    }
                    if ($splitShipments) {
                        $addressExtensionAttributes->setCalcuratesSplitShipments($splitShipments);
                    }
                }
                $address->setExtensionAttributes($addressExtensionAttributes);
            }
        }
        return $order;
    }
}
