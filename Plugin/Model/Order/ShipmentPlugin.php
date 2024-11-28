<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Model\Order;

use Calcurates\ModuleMagento\Model\ShippingLabel;
use Calcurates\ModuleMagento\Observer\ShipmentSaveAfterObserver;
use Magento\Sales\Model\Order\Shipment;

class ShipmentPlugin
{
    /**
     * Workaround to set track title
     * @TODO: remove all and get info from shipping label table
     *
     * @param Shipment $subject
     * @param Shipment\Track $track
     */
    public function beforeAddTrack(Shipment $subject, \Magento\Sales\Model\Order\Shipment\Track $track)
    {
        /** @var ShippingLabel|null $shippingLabel */
        $shippingLabel = $subject->getData(ShipmentSaveAfterObserver::SHIPPING_LABEL_KEY);

        if ($shippingLabel) {
            $track->setTitle(
                $shippingLabel->getShippingCarrierLabel() . ' - ' . $shippingLabel->getShippingServiceLabel()
            );
        }
    }
}
