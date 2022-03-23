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
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\GetQuoteDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\SaveQuoteDataInterface;
use Calcurates\ModuleMagento\Client\Response\DeliveryDateProcessor;
use Calcurates\ModuleMagento\Model\Carrier;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;

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
     * @var DeliveryDateProcessor
     */
    private $deliveryDateProcessor;

    public function __construct(
        GetQuoteDataInterface $getQuoteData,
        SaveQuoteDataInterface $saveQuoteData,
        QuoteDataInterfaceFactory $quoteDataFactory,
        DeliveryDateProcessor $deliveryDateProcessor
    ) {
        $this->getQuoteData = $getQuoteData;
        $this->saveQuoteData = $saveQuoteData;
        $this->quoteDataFactory = $quoteDataFactory;
        $this->deliveryDateProcessor = $deliveryDateProcessor;
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

        $deliveryDatesData = $quoteData->getDeliveryDates();

        $quoteData->setQuoteId((int)$cartId);
        $quoteData->setDeliveryDate('');
        $quoteData->setDeliveryDateFee(0.0);
        $quoteData->setDeliveryDateTimeTo('');
        $quoteData->setDeliveryDateTimeFrom('');
        $quoteData->setDeliveryDateTimeFee(0.0);

        try {
            $splitShipments = $addressInformation->getExtensionAttributes()->getCalcuratesSplitShipments();
            $splitShipmentArray = [];
            foreach ($splitShipments as $splitShipment) {
                $splitShipmentArray[] = $splitShipment->__toArray();
            }
            $quoteData->setSplitShipments($splitShipmentArray);
        } catch (\Exception $exception) {
        }
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
