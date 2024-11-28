<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\Model\Quote;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;

class DoNotUseBillingAddressForShipping
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        CartRepositoryInterface $cartRepository
    ) {
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param BillingAddressManagementInterface $subject
     * @param int $cartId
     * @param AddressInterface $address
     * @param bool $useForShipping
     * @return array
     * @throws NoSuchEntityException
     */
    public function beforeAssign(
        BillingAddressManagementInterface $subject,
        int $cartId,
        AddressInterface $address,
        bool $useForShipping = false
    ): array {
        $quote = $this->cartRepository->getActive($cartId);

        if ($this->isInStorePickupDeliveryQuote($quote)) {
            $useForShipping = false;
        }

        return [$cartId, $address, $useForShipping];
    }

    /**
     * @param CartInterface $cart
     * @return bool
     */
    private function isInStorePickupDeliveryQuote(CartInterface $cart): bool
    {
        if (!$cart->getShippingAddress() || !$cart->getShippingAddress()->getShippingMethod()) {
            return false;
        }

        /** @var Quote $cart */
        return $cart->getShippingAddress()->getShippingMethod() === 'inStorePickup';
    }
}
