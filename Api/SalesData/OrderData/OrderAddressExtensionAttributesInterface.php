<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2022 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\SalesData\OrderData;

interface OrderAddressExtensionAttributesInterface
{
    public const ORDER_ADDRESS_EXTENSION_TABLE = 'calcurates_order_address_data';
    public const EXT_ATTRIBUTE_RESIDENTIAL_DELIVERY = 'residential_delivery';
    public const MAGENTO_ORDER_ADDRESS_ID = 'address_id';

    /**
     * @return int
     */
    public function getAddressId(): int;

    /**
     * @param int $addressId
     * @return OrderAddressExtensionAttributesInterface
     */
    public function setAddressId(int $addressId): OrderAddressExtensionAttributesInterface;

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
