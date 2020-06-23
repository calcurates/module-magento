<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Model\Order;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Calcurates\ModuleMagento\Api\Shipping\ShippingDataResolverInterface;
use Calcurates\ModuleMagento\Model\Carrier;
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
use Magento\Sales\Model\Order\Shipment;

class ShipmentPlugin
{
    /**
     * @var ShippingDataResolverInterface
     */
    private $shippingDataResolver;

    /**
     * ShipmentPlugin constructor.
     * @param ShippingDataResolverInterface $shippingDataResolver
     */
    public function __construct(ShippingDataResolverInterface $shippingDataResolver)
    {
        $this->shippingDataResolver = $shippingDataResolver;
    }

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

            if (strpos($shippingMethod->getMethod(), ShippingMethodManager::CARRIER) === 0) {
                $title = explode('-', $order->getShippingDescription());
                $title = trim(current($title));
                $track->setTitle($title);
            }

            $shippingData = $this->shippingDataResolver->getShippingData($subject);
            $track->setData(CustomSalesAttributesInterface::SERVICE_ID, $shippingData->getShippingServiceId());
        }
    }
}
