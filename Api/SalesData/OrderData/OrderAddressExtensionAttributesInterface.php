<?php

namespace Calcurates\ModuleMagento\Api\SalesData\OrderData;

interface OrderAddressExtensionAttributesInterface
{
    const ORDER_ADDRESS_EXTENSION_TABLE = 'calcurates_order_address_data';
    const EXT_ATTRIBUTE_RESIDENTIAL_DELIVERY = 'residential_delivery';
    const MAGENTO_ORDER_ADDRESS_ID = 'address_id';

    /**
     * @return int
     */
    public function getAddressId(): int;

    /**
     * @param $addressId
     * @return OrderAddressExtensionAttributesInterface
     */
    public function setAddressId($addressId): OrderAddressExtensionAttributesInterface;

    /**
     * @return int|null
     */
    public function getResidentialDelivery(): ?int;

    /**
     * @param int|null $residentialDelivery
     * @return OrderAddressExtensionAttributesInterface
     */
    public function setResidentialDelivery(?int $residentialDelivery): OrderAddressExtensionAttributesInterface;
}
