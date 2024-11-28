<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Block\Adminhtml\Order;

use Calcurates\ModuleMagento\Model\Carrier;
use Calcurates\ModuleMagento\Model\Shipment\CarriersSettingsProvider;
use Calcurates\ModuleMagento\ViewModel\SmartPackaging;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Shipping\Block\Adminhtml\Order\Packaging;

class PackagingPlugin
{
    /**
     * @var SmartPackaging
     */
    private $smartPackaging;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @param SmartPackaging $smartPackaging
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        SmartPackaging $smartPackaging,
        DataPersistorInterface $dataPersistor
    ) {
        $this->smartPackaging = $smartPackaging;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @param Packaging $subject
     */
    public function beforeToHtml(Packaging $subject)
    {
        if ($subject->getNameInLayout() !== 'shipment_packaging') {
            return;
        }

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

        $this->dataPersistor->clear(CarriersSettingsProvider::CARRIERS_SETTINGS_DATA_CODE);
        $subject->setTemplate('Calcurates_ModuleMagento::order/packaging/popup.phtml');
        $subject->setData('calcurates_smart_packaging', $this->smartPackaging);
    }
}
