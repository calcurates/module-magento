<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\DeliveryDateFee\Quote\Address\Total;

use Calcurates\ModuleMagento\Api\Data\QuoteDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\GetQuoteDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\SaveQuoteDataInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address\Total\Shipping;

class AddShippingFeeToShippingTotal
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
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        GetQuoteDataInterface $getQuoteData,
        SaveQuoteDataInterface $saveQuoteData,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->getQuoteData = $getQuoteData;
        $this->saveQuoteData = $saveQuoteData;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Add delivery date fee to shipping amount
     * @param Shipping $subject
     * @param Shipping $result
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return Shipping
     */
    public function afterCollect(
        Shipping $subject,
        Shipping $result,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $address = $shippingAssignment->getShipping()->getAddress();
        $method = $shippingAssignment->getShipping()->getMethod();

        if (!$method) {
            return $result;
        }

        $quoteData = $this->getQuoteData->get((int)$quote->getId());
        $this->handlePossibleFeeChanges($quoteData, $method);
        $baseFeeAmount = $this->getBaseFeeAmount($quoteData);

        if (!$baseFeeAmount) {
            return $result;
        }

        $found = false;

        foreach ($address->getAllShippingRates() as $rate) {
            if ($rate->getCode() === $method) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            return $result;
        }

        $feeAmount = $this->priceCurrency->convertAndRound($baseFeeAmount);

        $total->addTotalAmount($subject->getCode(), $feeAmount);
        $total->addBaseTotalAmount($subject->getCode(), $baseFeeAmount);

        return $result;
    }

    /**
     * @param QuoteDataInterface|null $quoteData
     * @return float
     */
    private function getBaseFeeAmount(?QuoteDataInterface $quoteData): float
    {
        if (!$quoteData) {
            return 0.0;
        }

        $baseFeeAmount = $quoteData->getDeliveryDateFee() + $quoteData->getDeliveryDateTimeFee();

        return (float)$baseFeeAmount;
    }

    /**
     * @param QuoteDataInterface|null $quoteData
     * @param string $method
     * @return void
     */
    private function handlePossibleFeeChanges(?QuoteDataInterface $quoteData, string $method)
    {

        $savedDate = $quoteData->getDeliveryDate();
        if (!$savedDate || empty($quoteData->getDeliveryDates()[$method]['timeSlots'])) {
            return;
        }

        $matchedDateSlot = array_filter(
            $quoteData->getDeliveryDates()[$method]['timeSlots'],
            function ($dateSlot) use ($savedDate) {
                return substr($dateSlot['date'], 0, 10) === substr($savedDate, 0, 10);
            }
        );
        $matchedDateSlot = reset($matchedDateSlot);
        $quoteData->setDeliveryDateFee($matchedDateSlot ? (float)$matchedDateSlot['extraFee'] : 0);
        $quoteData->setDeliveryDate($matchedDateSlot ? $matchedDateSlot['date'] : '');

        if ($matchedDateSlot && !empty($matchedDateSlot['time'])) {
            $matchedTimeSlot = array_filter(
                $matchedDateSlot['time'],
                function ($timeSlot) use ($quoteData) {
                    return ($quoteData->getDeliveryDateTimeFrom() === $timeSlot['from'])
                        && ($quoteData->getDeliveryDateTimeTo() === $timeSlot['to']);
                }
            );
            $matchedTimeSlot = reset($matchedTimeSlot);
            $quoteData->setDeliveryDateTimeFrom($matchedTimeSlot ? $matchedTimeSlot['from']: '');
            $quoteData->setDeliveryDateTimeTo($matchedTimeSlot ? $matchedTimeSlot['to']: '');
            $quoteData->setDeliveryDateTimeFee($matchedTimeSlot ? (float)$matchedTimeSlot['extraFee']: 0);
        }

        $this->saveQuoteData->save($quoteData);
    }
}
