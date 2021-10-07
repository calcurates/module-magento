<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\InStorePickup;

use Magento\Quote\Api\Data\AddressInterface;

class GetShippingAddressData
{
    /**
     * @return array
     */
    public function execute(): array
    {
        return [
            AddressInterface::SAME_AS_BILLING => false,
            AddressInterface::SAVE_IN_ADDRESS_BOOK => false,
            AddressInterface::CUSTOMER_ADDRESS_ID => null
        ];
    }
}
