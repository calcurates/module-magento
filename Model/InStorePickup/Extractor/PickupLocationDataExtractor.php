<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\InStorePickup\Extractor;

use Calcurates\ModuleMagento\Api\Data\InStorePickup\PickupLocationInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\DataObject\Copy;

class PickupLocationDataExtractor
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

    /**
     * @param array $pickupLocation
     * @return array
     */
    public function extract(array $pickupLocation): array
    {
        $data = $this->objectCopyService->getDataFromFieldset(
            'calcurates_convert_pickup_location_response',
            'to_calcurates_in_store_pickup_location',
            $pickupLocation
        );

        $data[PickupLocationInterface::KEY_REGION_ID] = $this->retrieveRegionId($data);
        $data[PickupLocationInterface::KEY_STREET] = $this->retrieveStreet($data);

        return $data;
    }

    /**
     * @param array $data
     * @return int|null
     */
    private function retrieveRegionId(array $data): ?int
    {
        $cacheKey = sprintf(
            '%s_%s',
            $data[PickupLocationInterface::KEY_COUNTRY_ID],
            $data[PickupLocationInterface::KEY_REGION_CODE]
        );

        if (!isset($this->regions[$cacheKey])) {
            $region = $this->regionFactory->create();
            $region->loadByCode(
                $data[PickupLocationInterface::KEY_REGION_CODE],
                $data[PickupLocationInterface::KEY_COUNTRY_ID]
            );
            $this->regions[$cacheKey] = (int) $region->getId() ?: null;
        }

        return $this->regions[$cacheKey];
    }

    /**
     * @param array $data
     * @return string
     */
    private function retrieveStreet(array $data): string
    {
        return trim(sprintf('%s %s', $data['addressLine1'] ?? '', $data['addressLine2'] ?? ''));
    }
}
