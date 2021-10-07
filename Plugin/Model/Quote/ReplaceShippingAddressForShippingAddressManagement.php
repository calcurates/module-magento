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
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryInStorePickupQuote\Model\IsPickupLocationShippingAddress;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\ShippingAddressManagementInterface;

/**
 * Replace Shipping Address with Pickup Location Shipping Address for Shipping Address Management service.
 */
class ReplaceShippingAddressForShippingAddressManagement
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var IsPickupLocationShippingAddress
     */
    private $isPickupLocationShippingAddress;

    /**
     * @var ToQuoteAddressConvertor
     */
    private $toQuoteAddressConvertor;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param IsPickupLocationShippingAddress $isPickupLocationShippingAddress
     * @param ToQuoteAddressConvertor $addressConverter
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        IsPickupLocationShippingAddress $isPickupLocationShippingAddress,
        ToQuoteAddressConvertor $toQuoteAddressConvertor
    ) {
        $this->cartRepository = $cartRepository;
        $this->isPickupLocationShippingAddress = $isPickupLocationShippingAddress;
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

//        if ($this->isPickupLocationShippingAddress->execute($pickupLocation, $address)) {
//            return [$cartId, $address];
//        }

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
