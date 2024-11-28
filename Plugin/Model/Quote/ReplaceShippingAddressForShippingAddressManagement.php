<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\Model\Quote;

use Calcurates\ModuleMagento\Api\Data\InStorePickup\PickupLocationInterface;
use Calcurates\ModuleMagento\Model\InStorePickup\Convertor\ToQuoteAddressConvertor;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\ShippingAddressManagementInterface;

class ReplaceShippingAddressForShippingAddressManagement
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ToQuoteAddressConvertor
     */
    private $toQuoteAddressConvertor;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param ToQuoteAddressConvertor $toQuoteAddressConvertor
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ToQuoteAddressConvertor $toQuoteAddressConvertor
    ) {
        $this->cartRepository = $cartRepository;
        $this->toQuoteAddressConvertor = $toQuoteAddressConvertor;
    }

    /**
     * @param ShippingAddressManagementInterface $subject
     * @param int $cartId
     * @param AddressInterface $address
     * @return array
     */
    public function beforeAssign(
        ShippingAddressManagementInterface $subject,
        int $cartId,
        AddressInterface $address
    ): array {
        if (!$this->isQuoteAddressHasPickupLocationCode($address)) {
            return [$cartId, $address];
        }

        $pickupLocation = $this->getPickupLocation($address);

        $address = $this->toQuoteAddressConvertor->convert($pickupLocation, $address);

        return [$cartId, $address];
    }

    /**
     * @param AddressInterface $address
     * @return PickupLocationInterface
     */
    private function getPickupLocation(AddressInterface $address): PickupLocationInterface
    {
        return $address->getExtensionAttributes()->getCalcuratesPickupLocationQuoteAddress();
    }

    /**
     * @param AddressInterface $address
     * @return bool
     */
    private function isQuoteAddressHasPickupLocationCode(AddressInterface $address): bool
    {
        return $address->getExtensionAttributes()
            && $address->getExtensionAttributes()->getCalcuratesPickupLocationQuoteAddress();
    }
}
