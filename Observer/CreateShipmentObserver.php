<?php

namespace Calcurates\ModuleMagento\Observer;

use Calcurates\ModuleMagento\Model\Carrier;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;

class CreateShipmentObserver implements ObserverInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * AddPickupTabObserver constructor.
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $action = $observer->getData('full_action_name');
        if ($action != 'adminhtml_order_shipment_new') {
            return;
        }

        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->registry->registry('current_shipment');
        if (!$shipment instanceof ShipmentInterface) {
            // no additional data to display
            return;
        }

        $order = $shipment->getOrder();
        if (!$order instanceof OrderInterface) {
            return;
        }

        if ($order->getIsVirtual() || !$order->getData('shipping_method')) {
            return;
        }

        $shippingMethod = $order->getShippingMethod(true);
        if ($shippingMethod->getData('carrier_code') !== Carrier::CODE) {
            return;
        }

        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getData('layout');
        // add custom shipment layout
        $layout->getUpdate()->addHandle('calcurates_order_shipment');
    }
}
