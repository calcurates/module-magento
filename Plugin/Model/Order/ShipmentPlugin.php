<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Model\Order;

use Calcurates\ModuleMagento\Model\Carrier;
use Magento\Sales\Model\Order\Shipment;

class ShipmentPlugin
{
    /**
     * Workaround to set track title
     *
     * @param Shipment $subject
     * @param Shipment\Track $track
     */
    public function beforeAddTrack(Shipment $subject, \Magento\Sales\Model\Order\Shipment\Track $track)
    {
        if ($track->getCarrierCode() === Carrier::CODE && empty($track->getTitle())) {
            $order = $subject->getOrder();
            $shippingMethod = $order->getShippingMethod(true);

            if (strpos($shippingMethod->getMethod(), 'carrier_') === 0) {
                $title = explode('-', $order->getShippingDescription());
                $title = trim(current($title));
                $track->setTitle($title);
            }
        }
    }
}
