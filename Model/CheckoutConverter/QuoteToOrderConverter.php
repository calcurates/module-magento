<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\CheckoutConverter;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Calcurates\ModuleMagento\Api\SalesData\OrderData\OrderAddressExtensionAttributesInterfaceFactory;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\GetQuoteDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\QuoteAddressExtensionAttributesInterface;
use Calcurates\ModuleMagento\Model\Carrier;
use Calcurates\ModuleMagento\Model\Carrier\Method\CarrierData;
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class QuoteToOrderConverter
{
    /**
     * @var ConvertPackages
     */
    private $convertPackages;

    /**
     * @var ConvertServicesSources
     */
    private $convertServicesSources;

    /**
     * @var ShippingMethodManager
     */
    private $shippingMethodManager;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ConvertQuoteData
     */
    private $convertQuoteData;

    /**
     * @var OrderAddressExtensionAttributesInterfaceFactory
     */
    private $orderAddressFactory;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var GetQuoteDataInterface
     */
    private $getQuoteData;

    /**
     * QuoteToOrderConverter constructor.
     * @param ConvertPackages $convertPackages
     * @param ConvertServicesSources $convertServicesSources
     * @param ShippingMethodManager $shippingMethodManager
     * @param OrderRepositoryInterface $orderRepository
     * @param ConvertQuoteData $convertQuoteData
     * @param OrderAddressExtensionAttributesInterfaceFactory $orderAddressFactory
     * @param EntityManager $entityManager
     * @param GetQuoteDataInterface $getQuoteData
     */
    public function __construct(
        ConvertPackages $convertPackages,
        ConvertServicesSources $convertServicesSources,
        ShippingMethodManager $shippingMethodManager,
        OrderRepositoryInterface $orderRepository,
        ConvertQuoteData $convertQuoteData,
        OrderAddressExtensionAttributesInterfaceFactory $orderAddressFactory,
        EntityManager $entityManager,
        GetQuoteDataInterface $getQuoteData
    ) {
        $this->orderAddressFactory = $orderAddressFactory;
        $this->convertPackages = $convertPackages;
        $this->convertServicesSources = $convertServicesSources;
        $this->shippingMethodManager = $shippingMethodManager;
        $this->orderRepository = $orderRepository;
        $this->convertQuoteData = $convertQuoteData;
        $this->entityManager = $entityManager;
        $this->getQuoteData = $getQuoteData;
    }

    /**
     * @param Quote $quote
     * @param Order $order
     */
    public function convert(Quote $quote, Order $order): void
    {
        // @TODO: store all data in external table
        $orderChanged = false;

        $carrierData = $this->shippingMethodManager->getCarrierData(
            $order->getShippingMethod(false),
            '',
            $quote->getData(CustomSalesAttributesInterface::CARRIER_SOURCE_CODE_TO_SERVICE)
        );

        if (!$carrierData) {
            $quoteData = $this->getQuoteData->get((int)$quote->getId());
            if ($quoteData && $splitShipments = $quoteData->getSplitShipments()) {
                foreach ($splitShipments as $splitShipment) {
                    if (!isset($splitShipment['method'])) {
                        continue;
                    }
                    $carrierData[] = $this->shippingMethodManager->getCarrierData(
                        Carrier::CODE . '_' . $splitShipment['method'],
                        '',
                        $quote->getData(CustomSalesAttributesInterface::CARRIER_SOURCE_CODE_TO_SERVICE)
                    );
                }
            }
        }

        if ($carrierData instanceof CarrierData) {
            $this->convertPackages->convert($quote, $order, $carrierData);
            $this->convertServicesSources->convert($quote, $order);
            $orderChanged = true;
        } elseif (is_array($carrierData)) {
            foreach ($carrierData as $carrier) {
                if (!$carrier instanceof CarrierData) {
                    continue;
                }
                $this->convertPackages->convert($quote, $order, $carrier);
                $this->convertServicesSources->convert($quote, $order);
                $orderChanged = true;
            }
        }
        if ($quote->getShippingAddress()
            && $quote->getShippingAddress()->getExtensionAttributes()
        ) {
            $residentialDelivery = $quote->getShippingAddress()->getExtensionAttributes()->getResidentialDelivery();
            if ($residentialDelivery instanceof QuoteAddressExtensionAttributesInterface) {
                $residentialDeliveryExtension = $this->orderAddressFactory->create()
                    ->setAddressId((int) $order->getShippingAddress()->getEntityId())
                    ->setResidentialDelivery($residentialDelivery->getResidentialDelivery());
                $orderAddressExtension = $order
                    ->getShippingAddress()
                    ->getExtensionAttributes()
                    ->setResidentialDelivery($residentialDeliveryExtension);
                ;
                $order->getShippingAddress()->setExtensionAttributes($orderAddressExtension);
                $this->entityManager->save($residentialDeliveryExtension);
            }
        }
        $this->convertQuoteData->convert($quote, $order);

        if ($orderChanged) {
            $this->orderRepository->save($order);
        }
    }
}
