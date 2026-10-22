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
use Calcurates\ModuleMagento\Api\Data\QuoteDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\OrderData\SaveOrderDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\GetQuoteDataInterface;
use Calcurates\ModuleMagento\Model\Carrier;
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
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
            $orderData->setDeliveryDateLabel($quoteData->getDeliveryDateLabel());
            $orderData->setDeliveryDateTimeFrom($quoteData->getDeliveryDateTimeFrom());
            $orderData->setDeliveryDateTimeTo($quoteData->getDeliveryDateTimeTo());
            $orderData->setDeliveryDateTimeLabel($quoteData->getDeliveryDateTimeLabel());

            $baseFeeAmount = $quoteData->getDeliveryDateFee() + $quoteData->getDeliveryDateTimeFee();
            $feeAmount = $this->priceCurrency->convertAndRound($baseFeeAmount);

            $orderData->setBaseDeliveryDateFeeAmount($baseFeeAmount);
            $orderData->setDeliveryDateFeeAmount($feeAmount);

            $deliveryDates = $quoteData->getDeliveryDates();
            $deliveryDatesForCurrentShippingMethod = $deliveryDates[$order->getShippingMethod(false)] ?? [];
            $orderData->setDeliveryDates($deliveryDatesForCurrentShippingMethod);

            $orderData->setSplitShipments($quoteData->getSplitShipments());
            $orderData->setShippingRate(
                $this->resolveShippingRate($quoteData, (string)$order->getShippingMethod(false))
            );

            $this->saveOrderData->save($orderData);
        }
    }

    /**
     * Keep only the method the customer selected. Split checkout stores one snapshot per shipment.
     *
     * @param QuoteDataInterface $quoteData
     * @param string $methodCode
     * @return array
     */
    private function resolveShippingRate(QuoteDataInterface $quoteData, string $methodCode): array
    {
        $rates = $quoteData->getShippingRates();
        $selected = $rates[$methodCode] ?? [];
        if ($methodCode !== Carrier::CODE . '_' . ShippingMethodManager::META_RATE) {
            return $selected;
        }

        $shipments = [];
        foreach ($quoteData->getSplitShipments() as $splitShipment) {
            $childMethod = (string)($splitShipment['method'] ?? '');
            if ($childMethod === '') {
                continue;
            }
            $childCode = strpos($childMethod, Carrier::CODE . '_') === 0
                ? $childMethod
                : Carrier::CODE . '_' . $childMethod;
            if (!isset($rates[$childCode])) {
                continue;
            }
            $shipments[] = [
                'title' => (string)($splitShipment['title'] ?? ''),
                'method' => $childCode,
                'rate' => $rates[$childCode],
            ];
        }
        if ($shipments) {
            $selected['shipments'] = $shipments;
        }

        return $selected;
    }
}
