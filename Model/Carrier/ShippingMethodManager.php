<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Carrier;

use Calcurates\ModuleMagento\Model\Carrier;
use Calcurates\ModuleMagento\Model\Carrier\Method\CarrierDataFactory;
use Calcurates\ModuleMagento\Model\Carrier\Method\CarrierData;
use Calcurates\ModuleMagento\Model\Carrier\Method\InStorePickupDataFactory;
use Calcurates\ModuleMagento\Model\Carrier\Method\InStorePickupData;

class ShippingMethodManager
{
    public const FLAT_RATES = 'flatRate';
    public const FREE_SHIPPING = 'freeShipping';
    public const TABLE_RATE = 'tableRate';
    public const CARRIER = 'carrier';
    public const IN_STORE_PICKUP = 'inStorePickup';
    public const RATE_SHOPPING = 'rateShopping';

    /**
     * @var CarrierDataFactory
     */
    private $carrierDataFactory;

    /**
     * @var InStorePickupDataFactory
     */
    private $inStorePickupDataFactory;

    /**
     * @param CarrierDataFactory $carrierDataFactory
     * @param InStorePickupDataFactory $inStorePickupDataFactory
     */
    public function __construct(
        CarrierDataFactory $carrierDataFactory,
        InStorePickupDataFactory $inStorePickupDataFactory
    ) {
        $this->carrierDataFactory = $carrierDataFactory;
        $this->inStorePickupDataFactory = $inStorePickupDataFactory;
    }

    /**
     * @param string|null $shippingMethodFull
     * @param string $orderShippingDescription
     * @return CarrierData|null
     */
    public function getCarrierData(?string $shippingMethodFull, string $orderShippingDescription = ""): ?CarrierData
    {
        if (!$shippingMethodFull) {
            return null;
        }

        list($carrierCode, $method) = explode('_', $shippingMethodFull, 2);

        if ($carrierCode !== Carrier::CODE) {
            return null;
        }

        list($method, $additional) = explode('_', $method, 2);

        if ($method !== self::CARRIER) {
            return null;
        }

        list($carrierId, $serviceIds) = explode('_', $additional);

        $serviceIdsArray = explode(',', $serviceIds);

        $titleArray = explode('-', $orderShippingDescription);
        $carrierLabel = trim(array_shift($titleArray));
        $serviceLabel = trim(implode('-', $titleArray));

        return $this->carrierDataFactory->create([
            'data' => [
                CarrierData::CARRIER_ID => $carrierId,
                CarrierData::SERVICE_IDS_ARRAY => $serviceIdsArray,
                CarrierData::SERVICE_IDS_STRING => $serviceIds,
                CarrierData::CARRIER_LABEL => $carrierLabel,
                CarrierData::SERVICE_LABEL => $serviceLabel
            ]
        ]);
    }

    /**
     * @param string|null $shippingMethodCode
     * @return InStorePickupData|null
     */
    public function getInStorePickupData(?string $shippingMethodCode): ?InStorePickupData
    {
        if (!$shippingMethodCode) {
            return null;
        }

        list($method, $additional) = explode('_', $shippingMethodCode, 2);

        if ($method !== self::IN_STORE_PICKUP) {
            return null;
        }

        list($carrierId, $serviceId) = explode('_', $additional);

        return $this->inStorePickupDataFactory->create([
            'data' => [
                InStorePickupData::CARRIER_ID => $carrierId,
                InStorePickupData::SERVICE_ID => $serviceId,
            ]
        ]);
    }
}
