<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\Model\Checkout;

use Calcurates\ModuleMagento\Api\InStorePickup\PickupLocationRepositoryInterface;
use Calcurates\ModuleMagento\Model\Carrier;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class AddPickupLocationForQuoteAddress
{
    /**
     * @var PickupLocationRepositoryInterface
     */
    private $pickupLocationRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param PickupLocationRepositoryInterface $pickupLocationRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        PickupLocationRepositoryInterface $pickupLocationRepository,
        LoggerInterface $logger
    ) {
        $this->pickupLocationRepository = $pickupLocationRepository;
        $this->logger = $logger;
    }

    /**
     * @param ShippingInformationManagementInterface $subject
     * @param int $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return array
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagementInterface $subject,
        int $cartId,
        ShippingInformationInterface $addressInformation
    ): array {
        if ($addressInformation->getShippingCarrierCode() !== Carrier::CODE) {
            return [$cartId, $addressInformation];
        }

        list($shippingMethodCode, $calcuratesCarrierId, $calcuratesMethodId) = explode(
            '_',
            $addressInformation->getShippingMethodCode()
        );

        if ($shippingMethodCode !== 'inStorePickup') {
            return [$cartId, $addressInformation];
        }

        $shippingAddress = $addressInformation->getShippingAddress();
        $shippingAddressExtensionAttributes = $shippingAddress->getExtensionAttributes();
        if ($shippingAddressExtensionAttributes) {
            try {
                $pickupLocation = $this->pickupLocationRepository->getByShippingOptionId((int) $calcuratesMethodId);
                $shippingAddressExtensionAttributes->setCalcuratesPickupLocationQuoteAddress($pickupLocation);
                $shippingAddress->setExtensionAttributes($shippingAddressExtensionAttributes);

                $billingAddress = $addressInformation->getBillingAddress();
                $billingAddress->setData([]);
            } catch (NoSuchEntityException $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }

        return [$cartId, $addressInformation];
    }
}
