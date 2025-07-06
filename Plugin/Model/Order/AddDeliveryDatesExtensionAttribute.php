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
use Calcurates\ModuleMagento\Api\Data\Order\DeliveryDateInterfaceFactory;
use Calcurates\ModuleMagento\Api\Data\Order\DeliveryDate\CutOffTimeInterfaceFactory;

class AddDeliveryDatesExtensionAttribute
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
     * @var DeliveryDateInterfaceFactory
     */
    private $deliveryDateFactory;

    /**
     * @var CutOffTimeInterfaceFactory
     */
    private $cutOffTimeFactory;

    /**
     * AddDeliveryDatesExtensionAttribute constructor.
     * @param GetOrderDataInterface $getOrderData
     * @param DeliveryDateInterfaceFactory $deliveryDateInterfaceFactory
     * @param CutOffTimeInterfaceFactory $cutOffTimeInterfaceFactory
     * @param OrderAddressExtensionFactory $addressExtensionFactory
     */
    public function __construct(
        GetOrderDataInterface $getOrderData,
        DeliveryDateInterfaceFactory $deliveryDateInterfaceFactory,
        CutOffTimeInterfaceFactory $cutOffTimeInterfaceFactory,
        OrderAddressExtensionFactory $addressExtensionFactory
    ) {
        $this->addressExtensionFactory = $addressExtensionFactory;
        $this->cutOffTimeFactory = $cutOffTimeInterfaceFactory;
        $this->deliveryDateFactory = $deliveryDateInterfaceFactory;
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
                if ($orderData && $orderData->getDeliveryDates()) {
                    $deliveryDateData = $orderData->getDeliveryDates();
                    if (isset($deliveryDateData['cutOffTime']) && is_array($deliveryDateData['cutOffTime'])) {
                        $deliveryDateData['cutOffTime'] = $this->cutOffTimeFactory->create(
                            ['data' => $deliveryDateData['cutOffTime']]
                        );
                    }
                    $addressExtensionAttributes->setCalcuratesDeliveryDatesMetadata(
                        $this->deliveryDateFactory->create(['data' => $deliveryDateData])
                    );
                }
                $address->setExtensionAttributes($addressExtensionAttributes);
            }
        }
        return $order;
    }
}
