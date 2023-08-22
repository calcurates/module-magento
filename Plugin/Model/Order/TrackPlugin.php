<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Model\Order;

use Calcurates\ModuleMagento\Model\Carrier;
use Calcurates\ModuleMagento\Model\Carrier\Tracking\TrackingInfoProvider;
use Laminas\Json\Exception\RuntimeException;
use Magento\Shipping\Model\Order\Track;

class TrackPlugin
{
    /**
     * @var TrackingInfoProvider
     */
    private $trackingInfoProvider;

    public function __construct(
        TrackingInfoProvider $trackingInfoProvider
    ) {
        $this->trackingInfoProvider = $trackingInfoProvider;
    }

    /**
     * Workaround to get current track object in carrier get tracking details
     *
     * @param Track $subject
     * @param \Closure $proceed
     * @return mixed
     * @throws RuntimeException
     */
    public function aroundGetNumberDetail(Track $subject, \Closure $proceed)
    {
        if ($subject->getCarrierCode() === Carrier::CODE) {
            return $this->trackingInfoProvider->getTrackingInfo($subject);
        }

        return $proceed();
    }
}
