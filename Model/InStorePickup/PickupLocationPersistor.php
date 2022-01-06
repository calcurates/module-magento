<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\InStorePickup;

use Calcurates\ModuleMagento\Api\Data\InStorePickup\PickupLocationInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

class PickupLocationPersistor
{
    public const PICKUP_LOCATION_PERSISTENT_KEY = 'pickup_location';

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
