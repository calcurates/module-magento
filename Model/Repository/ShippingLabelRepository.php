<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Repository;

use Calcurates\ModuleMagento\Api\Data\ShippingLabelInterface;
use Calcurates\ModuleMagento\Api\Data\ShippingLabelInterfaceFactory;
use Calcurates\ModuleMagento\Api\ShippingLabelRepositoryInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\ShippingLabel as ShippingLabelResource;
use Calcurates\ModuleMagento\Model\ResourceModel\ShippingLabel\Collection;
use Calcurates\ModuleMagento\Model\ResourceModel\ShippingLabel\Collection as LabelCollection;
use Calcurates\ModuleMagento\Model\ResourceModel\ShippingLabel\CollectionFactory;
use Calcurates\ModuleMagento\Model\ShippingLabel;
use Magento\Framework\Exception\NoSuchEntityException;

class ShippingLabelRepository implements ShippingLabelRepositoryInterface
{
    /**
     * @var ShippingLabelResource
     */
    private $shippingLabelResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ShippingLabelInterfaceFactory
     */
    private $shippingLabelFactory;

    /**
     * ShippingLabelRepository constructor.
     * @param ShippingLabelResource $shippingLabelResource
     * @param CollectionFactory $collectionFactory
     * @param ShippingLabelInterfaceFactory $shippingLabelFactory
     */
    public function __construct(
        ShippingLabelResource $shippingLabelResource,
        CollectionFactory $collectionFactory,
        ShippingLabelInterfaceFactory $shippingLabelFactory
    ) {
        $this->shippingLabelResource = $shippingLabelResource;
        $this->collectionFactory = $collectionFactory;
        $this->shippingLabelFactory = $shippingLabelFactory;
    }

    /**
     * @param ShippingLabelInterface $shippingLabel
     * @return ShippingLabelInterface
     */
    public function save(ShippingLabelInterface $shippingLabel): ShippingLabelInterface
    {
        $this->shippingLabelResource->save($shippingLabel);

        return $shippingLabel;
    }

    /**
     * @param int $id
     * @return ShippingLabelInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): ShippingLabelInterface
    {
        $shippingLabel = $this->shippingLabelFactory->create();
        $this->shippingLabelResource->load($shippingLabel, $id);

        if (!$shippingLabel->getId()) {
            throw new NoSuchEntityException(__('No such shipping label with id %1', $id));
        }

        return $shippingLabel;
    }

    /**
     * @param int $shipmentId
     * @return Collection
     */
    public function getListByShipmentId(int $shipmentId)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ShippingLabelInterface::SHIPMENT_ID, $shipmentId);

        return $collection;
    }

    /**
     * @param int $shipmentId
     * @param string $trackingNumber
     * @return ShippingLabelInterface
     * @throws NoSuchEntityException
     */
    public function getByShipmentIdAndTrackingNumber(int $shipmentId, string $trackingNumber): ShippingLabelInterface
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ShippingLabelInterface::SHIPMENT_ID, $shipmentId);
        $collection->addFieldToFilter(ShippingLabelInterface::TRACKING_NUMBER, $trackingNumber);

        $item = $collection->getFirstItem();

        if (!$item->getId()) {
            throw new NoSuchEntityException(__('No such shipping label with tracking number %1', $trackingNumber));
        }

        return $item;
    }

    /**
     * Get list labels for shipments. Only one label for shipment (last label)
     * @param int[] $shipmentIds
     * @return ShippingLabelInterface[]
     */
    public function getListLastLabelsByShipments(array $shipmentIds): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ShippingLabelInterface::SHIPMENT_ID, ['in' => $shipmentIds]);
        $collection->addOrder(ShippingLabelInterface::ID, LabelCollection::SORT_ORDER_DESC);

        $labelToShipment = [];
        /** @var ShippingLabel $label */
        foreach ($collection->getItems() as $label) {
            if (isset($labelToShipment[$label->getShipmentId()])) {
                continue;
            }

            $labelToShipment[$label->getShipmentId()] = $label;
        }

        return $labelToShipment;
    }

    /**
     * Get last created label by shipment id
     * @param int $shipmentId
     * @return ShippingLabelInterface
     * @throws NoSuchEntityException
     */
    public function getLastByShipmentId(int $shipmentId): ShippingLabelInterface
    {
        $collection = $this->getListByShipmentId($shipmentId);
        $collection->addOrder(ShippingLabelInterface::ID, Collection::SORT_ORDER_DESC);

        $item = $collection->getFirstItem();

        if (!$item->getId()) {
            throw new NoSuchEntityException(__('No such shipping label for shipment id %1', $shipmentId));
        }

        return $item;
    }
}
