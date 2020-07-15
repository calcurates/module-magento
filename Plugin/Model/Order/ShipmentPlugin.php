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
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
use Calcurates\ModuleMagento\Model\ShippingLabel;
use Calcurates\ModuleMagento\Observer\ShipmentSaveAfterObserver;
use Magento\Sales\Model\Order\Shipment;

class ShipmentPlugin
{
    /**
     * @var ShippingDataResolverInterface
     */
    private $shippingDataResolver;

    /**
     * @var ShippingMethodManager
     */
    private $shippingMethodManager;

    /**
     * ShipmentPlugin constructor.
     * @param ShippingDataResolverInterface $shippingDataResolver
     * @param ShippingMethodManager $shippingMethodManager
     */
    public function __construct(
        ShippingDataResolverInterface $shippingDataResolver,
        ShippingMethodManager $shippingMethodManager
    ) {
        $this->shippingDataResolver = $shippingDataResolver;
        $this->shippingMethodManager = $shippingMethodManager;
    }

    /**
     * Workaround to set track title
     * @TODO: remove all and get info from shipping label table
     *
     * @param Shipment $subject
     * @param Shipment\Track $track
     */
    public function beforeAddTrack(Shipment $subject, \Magento\Sales\Model\Order\Shipment\Track $track)
    {
        $order = $subject->getOrder();
        $carrierData = $this->shippingMethodManager->getCarrierData(
            $order->getShippingMethod(false),
            $order->getShippingDescription()
        );

        if ($carrierData) {
            /** @var ShippingLabel|null $shippingLabel */
            $shippingLabel = $subject->getData(ShipmentSaveAfterObserver::SHIPPING_LABEL_KEY);

            if ($shippingLabel) {
                $track->setTitle(
                    $shippingLabel->getShippingCarrierLabel() . ' - ' . $shippingLabel->getShippingServiceLabel()
                );
            } else {
                $track->setTitle($carrierData->getCarrierLabel() . ' - ' . $carrierData->getServiceLabel());
            }

            $shippingData = $this->shippingDataResolver->getShippingData($subject);
            $track->setData(CustomSalesAttributesInterface::SERVICE_ID, $shippingData->getShippingServiceId());
        }
    }
}
