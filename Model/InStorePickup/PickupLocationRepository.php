<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\InStorePickup;

use Calcurates\ModuleMagento\Api\Data\InStorePickup\PickupLocationInterface;
use Calcurates\ModuleMagento\Api\InStorePickup\PickupLocationRepositoryInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class PickupLocationRepository implements PickupLocationRepositoryInterface
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @inheritdoc
     */
    public function getByShippingOptionId(int $shippingOptionId): PickupLocationInterface
    {
        $locationCode = null;
        $locations = $this->dataPersistor->get(PickupLocationPersistor::PICKUP_LOCATION_PERSISTENT_KEY);
        /** @var PickupLocationInterface $location */
        foreach ($locations as $code => $location) {
            if ($location->getShippingOptionId() === $shippingOptionId) {
                $locationCode = $code;
                break;
            }
        }
        if (!isset($locationCode)) {
            throw new NoSuchEntityException(__('No such location with shipping_option_id: %1', $shippingOptionId));
        }
        return $locations[$locationCode];
    }

    /**
     * @inheritdoc
     */
    public function getByCode(string $code): PickupLocationInterface
    {
        $locations = $this->dataPersistor->get(PickupLocationPersistor::PICKUP_LOCATION_PERSISTENT_KEY);
        if (!isset($locations[$code])) {
            throw new NoSuchEntityException(__('No such location with code: %1', $code));
        }
        return $locations[$code];
    }

    /**
     * @inheritdoc
     */
    public function getList(): array
    {
        $locations = $this->dataPersistor->get(PickupLocationPersistor::PICKUP_LOCATION_PERSISTENT_KEY);
        if (empty($locations)) {
            return [];
        }
        return $locations;
    }
}
