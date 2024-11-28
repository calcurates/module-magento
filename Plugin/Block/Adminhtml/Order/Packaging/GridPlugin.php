<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Block\Adminhtml\Order\Packaging;

use Calcurates\ModuleMagento\Model\Carrier;
use Calcurates\ModuleMagento\ViewModel\Adminhtml\Shipment\HsCodeFieldRenderer;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Shipping\Block\Adminhtml\Order\Packaging\Grid;

class GridPlugin
{
    /**
     * @var HsCodeFieldRenderer
     */
    private $hsCodeFieldRenderer;

    /**
     * GridPlugin constructor.
     * @param HsCodeFieldRenderer $hsCodeFieldRenderer
     */
    public function __construct(
        HsCodeFieldRenderer $hsCodeFieldRenderer
    ) {
        $this->hsCodeFieldRenderer = $hsCodeFieldRenderer;
    }

    /**
     * @param Grid $subject
     */
    public function beforeToHtml(
        Grid $subject
    ) {
        $shipment = $subject->getShipment();
        if (!$shipment instanceof ShipmentInterface) {
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

        $subject->setTemplate('Calcurates_ModuleMagento::order/packaging/grid.phtml');
        $subject->setData('calcurates_hs_code_field_renderer', $this->hsCodeFieldRenderer);
    }
}
