<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\CheckoutConverter;

use Calcurates\ModuleMagento\Api\Data\OrderDataInterface;
use Calcurates\ModuleMagento\Api\Data\OrderDataInterfaceFactory;
use Calcurates\ModuleMagento\Api\SalesData\OrderData\SaveOrderDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\GetQuoteDataInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class ConvertQuoteData
{
    /**
     * @var GetQuoteDataInterface
     */
    private $getQuoteData;

    /**
     * @var SaveOrderDataInterface
     */
    private $saveOrderData;

    /**
     * @var OrderDataInterfaceFactory
     */
    private $orderDataFactory;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        GetQuoteDataInterface $getQuoteData,
        SaveOrderDataInterface $saveOrderData,
        OrderDataInterfaceFactory $orderDataFactory,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->getQuoteData = $getQuoteData;
        $this->saveOrderData = $saveOrderData;
        $this->orderDataFactory = $orderDataFactory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param Quote $quote
     * @param Order $order
     */
    public function convert(Quote $quote, Order $order): void
    {
        $quoteData = $this->getQuoteData->get((int)$quote->getId());

        if ($quoteData) {
            /** @var OrderDataInterface $orderData */
            $orderData = $this->orderDataFactory->create();
            $orderData->setOrderId((int)$order->getId());
            $orderData->setDeliveryDate($quoteData->getDeliveryDate());
            $orderData->setDeliveryDateTimeFrom($quoteData->getDeliveryDateTimeFrom());
            $orderData->setDeliveryDateTimeTo($quoteData->getDeliveryDateTimeTo());

            $baseFeeAmount = $quoteData->getDeliveryDateFee() + $quoteData->getDeliveryDateTimeFee();
            $feeAmount = $this->priceCurrency->convertAndRound($baseFeeAmount);

            $orderData->setBaseDeliveryDateFeeAmount($baseFeeAmount);
            $orderData->setDeliveryDateFeeAmount($feeAmount);

            $deliveryDates = $quoteData->getDeliveryDates();
            $deliveryDatesForCurrentShippingMethod = $deliveryDates[$order->getShippingMethod(false)] ?? [];
            $orderData->setDeliveryDates($deliveryDatesForCurrentShippingMethod);

            $this->saveOrderData->save($orderData);
        }
    }
}
