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
use Calcurates\ModuleMagento\Model\ResourceModel\ShippingLabel\CollectionFactory;
use Calcurates\ModuleMagento\Model\ResourceModel\ShippingLabel\Collection;
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
}
