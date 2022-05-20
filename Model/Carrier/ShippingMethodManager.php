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
use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Magento\Framework\Serialize\SerializerInterface;

class ShippingMethodManager
{
    public const FLAT_RATES = 'flatRate';
    public const MERGRED_SHIPPING = 'mergedRate';
    public const FREE_SHIPPING = 'freeShipping';
    public const TABLE_RATE = 'tableRate';
    public const CARRIER = 'carrier';
    public const IN_STORE_PICKUP = 'inStorePickup';
    public const RATE_SHOPPING = 'rateShopping';
    public const META_RATE = 'metaRate';

    /**
     * @var CarrierDataFactory
     */
    private $carrierDataFactory;

    /**
     * @var InStorePickupDataFactory
     */
    private $inStorePickupDataFactory;

    private $serializer;

    /**
     * ShippingMethodManager constructor.
     * @param CarrierDataFactory $carrierDataFactory
     * @param InStorePickupDataFactory $inStorePickupDataFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CarrierDataFactory $carrierDataFactory,
        InStorePickupDataFactory $inStorePickupDataFactory,
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
        $this->carrierDataFactory = $carrierDataFactory;
        $this->inStorePickupDataFactory = $inStorePickupDataFactory;
    }

    /**
     * @param string|null $shippingMethodFull
     * @param string $orderShippingDescription
     * @param string $sourceCodeToService
     * @return array|CarrierData|null
     */
    public function getCarrierData(
        ?string $shippingMethodFull,
        string $orderShippingDescription = "",
        $sourceCodeToService = ''
    ) {
        if (!$shippingMethodFull) {
            return null;
        }

        list($carrierCode, $method) = explode('_', $shippingMethodFull, 2);
        if ($carrierCode !== Carrier::CODE) {
            return null;
        }
        $carrierData = $this->retrieveCarrierData($method, $orderShippingDescription);
        if (!$carrierData) {
            $carrierData = $this->retrieveCarrierDataFromMergedOption($method, $sourceCodeToService);
        }
        return $carrierData;
    }

    /**
     * @param string $method
     * @param string $orderShippingDescription
     * @return CarrierData|null
     */
    private function retrieveCarrierData($method, $orderShippingDescription)
    {
        try {
            list($method, $additional) = explode('_', $method, 2);
        } catch (\Exception $exception) {
            return null;
        }

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
     * @param string $method
     * @param string $sourceCodeToService
     * @return array|null
     */
    private function retrieveCarrierDataFromMergedOption($method, $sourceCodeToService)
    {
        if (!$sourceCodeToService) {
            return null;
        }
        try {
            list($method, $additional) = explode('_', $method, 2);
        } catch (\Exception $exception) {
            return null;
        }

        if ($method !== self::MERGRED_SHIPPING) {
            return null;
        }
        list($method, $additional) = explode('_', $additional, 2);
        if ($method !== self::CARRIER) {
            return null;
        }
        list($carrierIds) = explode('_', $additional);
        $carriers = explode(',', $carrierIds);
        $carrierData = [];
        foreach ($carriers as $carrierId) {
            $carrierServices = $this->serializer->unserialize($sourceCodeToService);
            $serviceIds = [];
            foreach ($carrierServices as $carrierDataId => $serviceData) {
                if ($carrierDataId == $carrierId) {
                    foreach ($serviceData as $serviceId => $sourceData) {
                        $serviceCombination = explode(',', $serviceId);
                        foreach ($serviceCombination as $serviceIdentity) {
                            $serviceIds[] = $serviceIdentity;
                        }
                    }
                }
            }
            $carrierData[] = $this->carrierDataFactory->create([
                'data' => [
                    CarrierData::CARRIER_ID => $carrierId,
                    CarrierData::SERVICE_IDS_ARRAY => $serviceIds,
                    CarrierData::SERVICE_IDS_STRING => implode(',', $serviceIds),
                    CarrierData::CARRIER_LABEL => 'Merged Carrier Option',
                    CarrierData::SERVICE_LABEL => 'Merged Carrier Option'
                ]
            ]);
        }
        return $carrierData ?: null;
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

        try {
            list($method, $additional) = explode('_', $shippingMethodCode, 2);
        } catch (\Exception $exception) {
            return null;
        }

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
