<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\InStorePickup;

use Calcurates\ModuleMagento\Api\Data\InStorePickup\AddressInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

class PickupLocationShippingAddressResolver
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
     * @param string $code
     * @return AddressInterface|null
     */
    public function get(string $code): ?AddressInterface
    {
        $addresses = $this->dataPersistor->get(PickupLocationShippingAddressPersistor::PERSISTENT_KEY);
        return $addresses[$code] ?? null;
    }
}
