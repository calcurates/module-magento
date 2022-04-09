<?php

namespace Calcurates\ModuleMagento\Api\SalesData\QuoteData;

interface QuoteAddressExtensionAttributesInterface
{
    const QUOTE_ADDRESS_EXTENSION_TABLE = 'calcurates_quote_address_data';
    const EXT_ATTRIBUTE_RESIDENTIAL_DELIVERY = 'residential_delivery';
    const MAGENTO_QUOTE_ADDRESS_ID = 'address_id';

    /**
     * @return int
     */
    public function getAddressId(): int;

    /**
     * @param $addressId
     * @return QuoteAddressExtensionAttributesInterface
     */
    public function setAddressId($addressId): QuoteAddressExtensionAttributesInterface;

    /**
     * @return int|null
     */
    public function getResidentialDelivery(): ?int;

    /**
     * @param int|null $residentialDelivery
     * @return QuoteAddressExtensionAttributesInterface
     */
    public function setResidentialDelivery(?int $residentialDelivery): QuoteAddressExtensionAttributesInterface;
}
