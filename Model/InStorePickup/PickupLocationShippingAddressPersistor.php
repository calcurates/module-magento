<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\InStorePickup;

use Calcurates\ModuleMagento\Api\Data\InStorePickup\AddressInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

class PickupLocationShippingAddressPersistor
{
    const PERSISTENT_KEY = 'pickup_location';

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
     * @param AddressInterface $shippingAddress
     */
    public function save(AddressInterface $shippingAddress)
    {
        $addresses = $this->dataPersistor->get(self::PERSISTENT_KEY);
        $addresses[$shippingAddress->getCode()] = $shippingAddress;

        $this->dataPersistor->set(self::PERSISTENT_KEY, $addresses);
    }
}
