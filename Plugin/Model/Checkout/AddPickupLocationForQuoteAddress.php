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
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
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
     * @var ShippingMethodManager
     */
    private $shippingMethodManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param PickupLocationRepositoryInterface $pickupLocationRepository
     * @param ShippingMethodManager $shippingMethodManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        PickupLocationRepositoryInterface $pickupLocationRepository,
        ShippingMethodManager $shippingMethodManager,
        LoggerInterface $logger
    ) {
        $this->pickupLocationRepository = $pickupLocationRepository;
        $this->shippingMethodManager = $shippingMethodManager;
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

        $inStorePickupData = $this->shippingMethodManager->getInStorePickupData(
            $addressInformation->getShippingMethodCode()
        );

        if ($inStorePickupData === null) {
            return [$cartId, $addressInformation];
        }

        $shippingAddress = $addressInformation->getShippingAddress();
        $shippingAddressExtensionAttributes = $shippingAddress->getExtensionAttributes();
        if ($shippingAddressExtensionAttributes) {
            try {
                $pickupLocation = $this->pickupLocationRepository->getByShippingOptionId(
                    $inStorePickupData->getServiceId()
                );
                $shippingAddressExtensionAttributes->setCalcuratesPickupLocationQuoteAddress($pickupLocation);
                $shippingAddress->setExtensionAttributes($shippingAddressExtensionAttributes);

                $billingAddress = $addressInformation->getBillingAddress();
                $billingAddress->setData(null);
            } catch (NoSuchEntityException $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }

        return [$cartId, $addressInformation];
    }
}
