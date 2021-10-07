<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\InStorePickup\Convertor;

use Calcurates\ModuleMagento\Api\Data\InStorePickup\PickupLocationInterface;
use Calcurates\ModuleMagento\Model\InStorePickup\Extractor\PickupLocationShippingAddressDataExtractor;
use Calcurates\ModuleMagento\Model\InStorePickup\GetShippingAddressData;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject\Copy;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\AddressFactory;

class ToQuoteAddressConvertor
{
    /**
     * @var PickupLocationShippingAddressDataExtractor
     */
    private $pickupLocationShippingAddressDataExtractor;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var GetShippingAddressData
     */
    private $getShippingAddressData;

    /**
     * @param Copy $objectCopyService
     * @param PickupLocationShippingAddressDataExtractor $pickupLocationShippingAddressDataExtractor
     * @param AddressFactory $addressFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param GetShippingAddressData $getShippingAddressData
     */
    public function __construct(
        Copy $objectCopyService,
        PickupLocationShippingAddressDataExtractor $pickupLocationShippingAddressDataExtractor,
        AddressFactory $addressFactory,
        DataObjectHelper $dataObjectHelper,
        GetShippingAddressData $getShippingAddressData
    ) {
        $this->pickupLocationShippingAddressDataExtractor = $pickupLocationShippingAddressDataExtractor;
        $this->objectCopyService = $objectCopyService;
        $this->addressFactory = $addressFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->getShippingAddressData = $getShippingAddressData;
    }

    /**
     * @param PickupLocationInterface $pickupLocation
     * @param AddressInterface $originalAddress
     * @param array $data
     * @return AddressInterface
     */
    public function convert(
        PickupLocationInterface $pickupLocation,
        AddressInterface $originalAddress,
        array $data = []
    ): AddressInterface {
        $pickupLocationAddressData = $this->getShippingAddressData->execute()
            + $this->pickupLocationShippingAddressDataExtractor->extract($pickupLocation);

        $quoteAddressData = $this->objectCopyService->getDataFromFieldset(
            'sales_convert_quote_address',
            'to_calcurates_in_store_pickup_shipping_address',
            $originalAddress
        );

        $address = $this->addressFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $address,
            array_merge($pickupLocationAddressData, $quoteAddressData, $data),
            AddressInterface::class
        );

        return $address;
    }
}
