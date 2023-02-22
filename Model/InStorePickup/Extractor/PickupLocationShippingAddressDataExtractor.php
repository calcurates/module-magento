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
use Magento\Framework\DataObject\Copy;

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
