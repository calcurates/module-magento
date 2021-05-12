<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\ShippingLabel\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Shipment;
use Magento\Shipping\Model\Shipping\LabelGenerator;

class LabelGeneratorPlugin
{
    /**
     * Add additional request data to shipment before create label
     *
     * @param LabelGenerator $subject
     * @param Shipment $shipment
     * @param RequestInterface $request
     * @throws LocalizedException
     */
    public function beforeCreate(
        LabelGenerator $subject,
        Shipment $shipment,
        RequestInterface $request
    ) {
        $shippingServiceId = $request->getParam('calcuratesShippingServiceId');

        if (!$shippingServiceId) {
            throw new LocalizedException(__('Invalid Shipping Method'));
        }

        $shipment->setData('calcuratesShippingServiceId', (int)$shippingServiceId);
    }
}
