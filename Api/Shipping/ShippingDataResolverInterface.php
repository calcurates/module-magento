<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */
namespace Calcurates\ModuleMagento\Api\Shipping;

use Calcurates\ModuleMagento\Api\Data\ShippingDataInterface;
use Magento\Sales\Api\Data\ShipmentInterface;

interface ShippingDataResolverInterface
{
    /**
     * @param ShipmentInterface $shipment
     * @return ShippingDataInterface
     */
    public function getShippingData(ShipmentInterface $shipment);
}
