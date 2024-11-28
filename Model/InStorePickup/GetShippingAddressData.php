<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

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
