<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Observer;

use Calcurates\ModuleMagento\Api\ShippingLabelRepositoryInterface;
use Calcurates\ModuleMagento\Model\ShippingLabel;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Shipment;

/**
 * Save calcurates shipping label object only after saving shipment, because before we haven't any shipment_id
 */
class ShipmentSaveAfterObserver implements ObserverInterface
{
    public const SHIPPING_LABEL_KEY = 'calcurates_shipping_label';

    /**
     * @var ShippingLabelRepositoryInterface
     */
    private $shippingLabelRepository;

    /**
     * ShipmentSaveAfterObserver constructor.
     * @param ShippingLabelRepositoryInterface $shippingLabelRepository
     */
    public function __construct(ShippingLabelRepositoryInterface $shippingLabelRepository)
    {
        $this->shippingLabelRepository = $shippingLabelRepository;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Shipment $shipment */
        $shipment = $observer->getData('shipment');

        /** @var ShippingLabel|null $shippingLabel */
        $shippingLabel = $shipment->getData(self::SHIPPING_LABEL_KEY);

        if ($shippingLabel && !$shippingLabel->getId()) {
            $shippingLabel->setShipmentId((int)$shipment->getId());
            $this->shippingLabelRepository->save($shippingLabel);
        }
    }
}
