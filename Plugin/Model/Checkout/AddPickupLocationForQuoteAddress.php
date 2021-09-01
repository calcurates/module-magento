<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\Model\Checkout;

use Calcurates\ModuleMagento\Client\Command\GetAllShippingOptionsCommand;
use Calcurates\ModuleMagento\Client\Command\GetShippingOptionsCommand;
use Calcurates\ModuleMagento\Model\Carrier;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;

/**
 * Class AddPickupLocationForQuoteAddress
 */
class AddPickupLocationForQuoteAddress
{
    /**
     * @var GetShippingOptionsCommand
     */
    private $getShippingOptionsCommand;

    /**
     * @var GetAllShippingOptionsCommand
     */
    private $getAllShippingOptionsCommand;

    /**
     * AddPickupLocationForQuoteAddress constructor.
     * @param GetShippingOptionsCommand $getShippingOptionsCommand
     * @param GetAllShippingOptionsCommand $getAllShippingOptionsCommand
     */
    public function __construct(
        GetShippingOptionsCommand $getShippingOptionsCommand,
        GetAllShippingOptionsCommand $getAllShippingOptionsCommand
    ) {
        $this->getShippingOptionsCommand = $getShippingOptionsCommand;
        $this->getAllShippingOptionsCommand = $getAllShippingOptionsCommand;
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

        list($shippingMethodCode, $CalcuratesCarrierId, $CalcuratesMethodId) = explode(
            '_',
            $addressInformation->getShippingMethodCode()
        );

        if ($shippingMethodCode !== 'inStorePickup') {
            return [$cartId, $addressInformation];
        }

//        $shippingOptions = $this->getShippingOptionsCommand->get(
//            GetShippingOptionsCommand::TYPE_IN_STORE_PICKUP,
//            1
//        );

        $billingAddress = $addressInformation->getBillingAddress();
        $billingAddress->setData([]);

        return [$cartId, $addressInformation];
    }
}
