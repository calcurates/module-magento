<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\InStorePickup\Extractor;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\DataObject\Copy;
use Calcurates\ModuleMagento\Api\Data\InStorePickup\PickupLocationInterface;

class PickupLocationShippingAddressDataExtractor
{
    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @param Copy $copyService
     */
    public function __construct(
        Copy $copyService
    ) {
        $this->objectCopyService = $copyService;
    }

    /**
     * @param PickupLocationInterface $pickupLocation
     * @return array
     */
    public function extract(PickupLocationInterface $pickupLocation): array
    {
        return $this->objectCopyService->getDataFromFieldset(
            'calcurates_convert_pickup_location',
            'to_calcurates_in_store_pickup_shipping_address',
            $pickupLocation
        );
    }
}
