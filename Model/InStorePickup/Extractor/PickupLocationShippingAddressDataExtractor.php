<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\InStorePickup\Extractor;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\DataObject\Copy;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryInStorePickupApi\Api\Data\PickupLocationInterface;

class PickupLocationShippingAddressDataExtractor
{
    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var array
     */
    private $regions = [];

    /**
     * @param Copy $copyService
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        Copy $copyService,
        RegionFactory $regionFactory
    ) {
        $this->objectCopyService = $copyService;
        $this->regionFactory = $regionFactory;
    }

    public function execute(array $pickupLocation): array
    {
        $data = $this->objectCopyService->getDataFromFieldset(
            'calcurates_convert_pickup_location',
            'to_calcurates_in_store_pickup_address',
            $pickupLocation
        );
        //$data = $this->retrieveRegion($pickupLocation, $data);

        return $data;
    }

//    private function retrieveRegion(array $pickupLocation, array $data): array
//    {
//        $cacheKey = $pickupLocation[country_Id() . '_' . $pickupLocation->getRegionId();
//
//        if (!isset($this->regions[$cacheKey])) {
//            $region = $this->regionFactory->create();
//            $region->loadByName($pickupLocation->getRegion(), $pickupLocation->getCountryId());
//            $this->regions[$cacheKey] = $region->getName() ?: $pickupLocation->getRegion();
//        }
//
//        $data[SourceInterface::REGION] = $this->regions[$cacheKey];
//
//        return $data;
//    }
}
