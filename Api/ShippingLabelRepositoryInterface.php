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
use Magento\Framework\Exception\NoSuchEntityException;

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

    /**
     * @param int $shipmentId
     * @param string $trackingNumber
     * @return ShippingLabelInterface
     * @throws NoSuchEntityException
     */
    public function getByShipmentIdAndTrackingNumber(int $shipmentId, string $trackingNumber): ShippingLabelInterface;

    /**
     * Get list labels for shipments. Only one label for shipment (last label)
     * @param int[] $shipmentIds
     * @return ShippingLabelInterface[] array (shipmentId => ShippingLabelInterface)
     */
    public function getListLastLabelsByShipments(array $shipmentIds): array;

    /**
     * Get last created label by shipment id
     * @param int $shipmentId
     * @return ShippingLabelInterface
     * @throws NoSuchEntityException
     */
    public function getLastByShipmentId(int $shipmentId): ShippingLabelInterface;
}
