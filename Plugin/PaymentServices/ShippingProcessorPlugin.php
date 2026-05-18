<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\PaymentServices;

use Calcurates\ModuleMagento\Model\Carrier;
use Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\PaymentServicesPaypal\Model\ShippingCallback\ShippingProcessor;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Calcurates\ModuleMagento\Api\ConfigProviderInterface;

class ShippingProcessorPlugin
{
    /**
     * @var ConfigProviderInterface
     */
    private $config;

    /**
     * @var ShippingInformationManagementInterface
     */
    private $shippingInformationManagement;

    /**
     * @var ShippingInformationInterfaceFactory
     */
    private $shippingInformationFactory;

    /**
     * ShippingProcessorPlugin constructor.
     * @param ConfigProviderInterface $config
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     * @param ShippingInformationInterfaceFactory $shippingInformationFactory
     */
    public function __construct(
        ConfigProviderInterface $config,
        ShippingInformationManagementInterface $shippingInformationManagement,
        ShippingInformationInterfaceFactory $shippingInformationFactory
    ) {
        $this->config = $config;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformationFactory = $shippingInformationFactory;
    }

    /**
     * @param ShippingProcessor $subject
     * @param array $result
     * @param CartInterface|Quote $quote
     */
    public function afterGetShippingMethods(
        ShippingProcessor $subject,
        array $result,
        CartInterface|Quote $quote
    ): array {
        $shippingRates = $quote->getShippingAddress()->getAllShippingRates();

        if (empty($shippingRates)) {
            return $result;
        }
        $calcuratesCarrierTitle = $this->config->getTitle($quote->getStoreId());
        $rateTitles = [];
        foreach ($shippingRates as $rate) {
            if ($rate->getCarrier() !== Carrier::CODE) {
                continue;
            }

            $id = $rate->getCarrier() . '_' . $rate->getMethod();
            $rateTitles[$id] = [
                'carrierTitle' => $calcuratesCarrierTitle,
                'methodTitle' => $rate->getMethodTitle(),
            ];
        }

        foreach ($result as &$method) {
            $methodId = $method['id'] ?? '';

            if (!isset($rateTitles[$methodId])) {
                continue;
            }

            $carrierTitle = $rateTitles[$methodId]['carrierTitle'];
            $methodTitle = $rateTitles[$methodId]['methodTitle'];

            if ($methodTitle && $methodTitle !== $carrierTitle) {
                $method['label'] = $carrierTitle . ' - ' . $methodTitle;
            } elseif ($methodTitle) {
                $method['label'] = $methodTitle;
            }
        }

        return $result;
    }


    /**
     * @param ShippingProcessor $subject
     * @param callable $proceed
     * @param CartInterface|Quote $quote
     */
    public function aroundProcessShippingOptions(
        ShippingProcessor $subject,
        callable $proceed,
        CartInterface|Quote $quote,
        array $shippingOption
    ): void {
        $methodId = $shippingOption['id'] ?? '';
        $carrierPrefix = Carrier::CODE . '_';

        if (!str_starts_with($methodId, $carrierPrefix)) {
            $proceed($quote, $shippingOption);
            return;
        }
        $methodCode = substr($methodId, strlen($carrierPrefix));
        $quote->getShippingAddress()->setShippingMethod($methodId);
        $quote->getShippingAddress()->setShippingAmount($shippingOption['amount']['value']);
        $shippingInformation = $this->shippingInformationFactory->create();
        $shippingInformation->setShippingAddress($quote->getShippingAddress());
        $shippingInformation->setBillingAddress($quote->getBillingAddress());
        $shippingInformation->setShippingCarrierCode(Carrier::CODE);
        $shippingInformation->setShippingMethodCode($methodCode);
        $this->shippingInformationManagement->saveAddressInformation(
            $quote->getId(),
            $shippingInformation
        );
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->getShippingAddress()->collectShippingRates();
    }
}
