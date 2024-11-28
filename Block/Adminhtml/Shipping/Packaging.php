<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Block\Adminhtml\Shipping;

use Calcurates\ModuleMagento\Model\Carrier;
use Calcurates\ModuleMagento\Model\Shipment\CustomPackagesProvider;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order\Shipment;

class Packaging extends Template
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var CustomPackagesProvider
     */
    private $customPackagesProvider;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * Create constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param CustomPackagesProvider $customPackagesProvider
     * @param Json $jsonSerializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        CustomPackagesProvider $customPackagesProvider,
        Json $jsonSerializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->customPackagesProvider = $customPackagesProvider;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Retrieve shipment model instance
     *
     * @return Shipment
     */
    public function getShipment()
    {
        return $this->coreRegistry->registry('current_shipment');
    }

    /**
     * @return bool
     */
    public function shippingMethodIsCalcurates()
    {
        $order = $this->getShipment()->getOrder();
        $shippingMethod = $order->getShippingMethod(true);
        if ($shippingMethod->getData('carrier_code') !== Carrier::CODE) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getCustomPackagesJson()
    {
        $customPackagesData = $this->customPackagesProvider->getCustomPackages(
            $this->getShipment()
        );

        return $this->jsonSerializer->serialize($customPackagesData);
    }
}
