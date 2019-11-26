<?php

namespace Calcurates\ModuleMagento\Plugin\Model\Order;

use Calcurates\ModuleMagento\Model\Carrier;
use Magento\Shipping\Model\Order\Track;

class TrackPlugin
{
    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    private $carrierFactory;

    /**
     * TrackPlugin constructor.
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     */
    public function __construct(\Magento\Shipping\Model\CarrierFactory $carrierFactory)
    {
        $this->carrierFactory = $carrierFactory;
    }

    /**
     * Workaround to get current track object in carrier get tracking details
     *
     * @param Track $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundGetNumberDetail(Track $subject, \Closure $proceed)
    {
        if ($subject->getCarrierCode() == Carrier::CODE) {
            $carrierInstance = $this->carrierFactory->create($subject->getCarrierCode());
            if (!$carrierInstance) {
                $custom = [];
                $custom['title'] = $subject->getTitle();
                $custom['number'] = $subject->getTrackNumber();
                return $custom;
            } else {
                $carrierInstance->setStore($subject->getStore());
            }

            $trackingInfo = $carrierInstance->getTrackingInfo($subject->getNumber(), $subject);
            if (!$trackingInfo) {
                return __('No detail for number "%1"', $subject->getNumber());
            }

            return $trackingInfo;
        }

        return $proceed();
    }
}
