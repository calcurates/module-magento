<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\QuoteDataSave\Checkout\Model;

use Calcurates\ModuleMagento\Api\Data\QuoteDataInterfaceFactory;
use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\GetQuoteDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\SaveQuoteDataInterface;
use Calcurates\ModuleMagento\Client\Response\DeliveryDateProcessor;
use Calcurates\ModuleMagento\Model\Carrier;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class SaveDeliveryDateFromShippingInfoPlugin
{
    /**
     * @var GetQuoteDataInterface
     */
    private $getQuoteData;

    /**
     * @var SaveQuoteDataInterface
     */
    private $saveQuoteData;

    /**
     * @var QuoteDataInterfaceFactory
     */
    private $quoteDataFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var DeliveryDateProcessor
     */
    private $deliveryDateProcessor;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        GetQuoteDataInterface $getQuoteData,
        SaveQuoteDataInterface $saveQuoteData,
        QuoteDataInterfaceFactory $quoteDataFactory,
        CartRepositoryInterface $cartRepository,
        DeliveryDateProcessor $deliveryDateProcessor,
        SerializerInterface $serializer
    ) {
        $this->getQuoteData = $getQuoteData;
        $this->saveQuoteData = $saveQuoteData;
        $this->quoteDataFactory = $quoteDataFactory;
        $this->cartRepository = $cartRepository;
        $this->deliveryDateProcessor = $deliveryDateProcessor;
        $this->serializer = $serializer;
    }

    /**
     * @param ShippingInformationManagementInterface $subject
     * @param int $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagementInterface $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ): void {
        $deliveryDateId = $addressInformation->getExtensionAttributes()->getCalcuratesDeliveryDateId();
        $timeId = $addressInformation->getExtensionAttributes()->getCalcuratesDeliveryDateTimeId();

        if ($addressInformation->getShippingCarrierCode() !== Carrier::CODE) {
            $deliveryDateId = $timeId = null;
        }

        $quoteData = $this->getQuoteData->get((int)$cartId);

        if (!$quoteData) {
            $quoteData = $this->quoteDataFactory->create();
        }

        $quote = $this->cartRepository->getActive($cartId);

        $deliveryDatesString = $quote->getData(CustomSalesAttributesInterface::DELIVERY_DATES);
        $deliveryDatesData = [];
        if ($deliveryDatesString) {
            $deliveryDatesData = $this->serializer->unserialize($deliveryDatesString);
        }

        $quoteData->setQuoteId((int)$cartId);
        $quoteData->setDeliveryDate('');
        $quoteData->setDeliveryDateFee(0.0);
        $quoteData->setDeliveryDateTimeTo('');
        $quoteData->setDeliveryDateTimeFrom('');
        $quoteData->setDeliveryDateTimeFee(0.0);

        $methodCode = $addressInformation->getShippingCarrierCode() . '_' . $addressInformation->getShippingMethodCode();
        if (isset($deliveryDatesData[$methodCode])) {
            $deliveryDates = $this->deliveryDateProcessor->getDeliveryDates($deliveryDatesData[$methodCode]['timeSlots'] ?? []);
            foreach ($deliveryDates as $deliveryDate) {
                if ($deliveryDateId === $deliveryDate->getId()) {
                    $quoteData->setDeliveryDate($deliveryDate->getDate());
                    $quoteData->setDeliveryDateFee($deliveryDate->getFeeAmount());
                    foreach ($deliveryDate->getTimeIntervals() as $timeInterval) {
                        if ($timeInterval->getId() === $timeId) {
                            $quoteData->setDeliveryDateTimeFrom($timeInterval->getFrom());
                            $quoteData->setDeliveryDateTimeTo($timeInterval->getTo());
                            $quoteData->setDeliveryDateTimeFee($timeInterval->getFeeAmount());
                            break;
                        }
                    }
                    break;
                }
            }
        }

        $this->saveQuoteData->save($quoteData);
    }
}
