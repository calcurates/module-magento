<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\InStorePickup;

use Calcurates\ModuleMagento\Api\Data\InStorePickup\PickupLocationInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

class PickupLocationPersistor
{
    const PICKUP_LOCATION_PERSISTENT_KEY = 'pickup_location';

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var PickupLocationInterface[]
     */
    private $locations = [];

    /**
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @param PickupLocationInterface $pickupLocation
     */
    public function save(PickupLocationInterface $pickupLocation)
    {
        if (!isset($this->locations[$pickupLocation->getCode()])) {
            $this->locations[$pickupLocation->getCode()] = $pickupLocation;
        }
        $this->dataPersistor->set(self::PICKUP_LOCATION_PERSISTENT_KEY, $this->locations);
    }
}
