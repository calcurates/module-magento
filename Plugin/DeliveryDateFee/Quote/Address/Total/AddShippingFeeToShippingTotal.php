<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\DeliveryDateFee\Quote\Address\Total;

use Calcurates\ModuleMagento\Api\SalesData\QuoteData\GetQuoteDataInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address\Total\Shipping;

class AddShippingFeeToShippingTotal
{
    /**
     * @var GetQuoteDataInterface
     */
    private $getQuoteData;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(GetQuoteDataInterface $getQuoteData, PriceCurrencyInterface $priceCurrency)
    {
        $this->getQuoteData = $getQuoteData;
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

        $baseFeeAmount = $this->getBaseFeeAmount((int)$quote->getId());

        if (!$baseFeeAmount) {
            return $result;
        }

        $found = false;

        foreach ($address->getAllShippingRates() as $rate) {
            if ($rate->getCode() == $method) {
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
     * @param int $quoteId
     * @return float
     */
    private function getBaseFeeAmount(int $quoteId): float
    {
        $quoteData = $this->getQuoteData->get($quoteId);

        if (!$quoteData) {
            return 0.0;
        }

        $baseFeeAmount = $quoteData->getDeliveryDateFee() + $quoteData->getDeliveryDateTimeFee();

        return (float)$baseFeeAmount;
    }
}
