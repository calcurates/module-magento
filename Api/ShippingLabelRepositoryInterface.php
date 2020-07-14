<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api;

use Calcurates\ModuleMagento\Api\Data\ShippingLabelInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\ShippingLabel\Collection;

interface ShippingLabelRepositoryInterface
{
    /**
     * @param ShippingLabelInterface $shippingLabel
     * @return ShippingLabelInterface
     */
    public function save(ShippingLabelInterface $shippingLabel): ShippingLabelInterface;

    /**
     * @param int $id
     * @return ShippingLabelInterface
     */
    public function getById(int $id): ShippingLabelInterface;

    /**
     * @param int $shipmentId
     * @return Collection
     */
    public function getListByShipmentId(int $shipmentId);
}
