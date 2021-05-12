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
     * ShippingMethodManager constructor.
     * @param CarrierDataFactory $carrierDataFactory
     */
    public function __construct(CarrierDataFactory $carrierDataFactory)
    {
        $this->carrierDataFactory = $carrierDataFactory;
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
}
