<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\ViewModel;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class SmartPackaging implements ArgumentInterface
{
    /**
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return float|mixed|null
     */
    public function getPackages(\Magento\Sales\Model\Order\Shipment $shipment): ?string
    {
        return $shipment->getOrder()->getData(CustomSalesAttributesInterface::CARRIER_PACKAGES);
    }
}
