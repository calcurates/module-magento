<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2022 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\SalesData\QuoteData;

interface QuoteAddressExtensionAttributesInterface
{
    public const QUOTE_ADDRESS_EXTENSION_TABLE = 'calcurates_quote_address_data';
    public const EXT_ATTRIBUTE_RESIDENTIAL_DELIVERY = 'residential_delivery';
    public const MAGENTO_QUOTE_ADDRESS_ID = 'address_id';

    /**
     * @return int
     */
    public function getAddressId(): int;

    /**
     * @param int $addressId
     * @return QuoteAddressExtensionAttributesInterface
     */
    public function setAddressId(int $addressId): QuoteAddressExtensionAttributesInterface;

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
