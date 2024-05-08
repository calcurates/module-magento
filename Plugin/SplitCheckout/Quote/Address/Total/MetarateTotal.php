<?php

namespace Calcurates\ModuleMagento\Plugin\SplitCheckout\Quote\Address\Total;

use Calcurates\ModuleMagento\Api\Data\MetaRateDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\GetQuoteDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\SaveQuoteDataInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\Shipping;

class MetarateTotal
{
    /**
     * @var MetaRateDataInterface
     */
    private $metarateData;

    /**
     * @var GetQuoteDataInterface
     */
    private $getQuoteData;

    /**
     * @var SaveQuoteDataInterface
     */
    private $saveQuoteData;

    /**
     * @param MetaRateDataInterface $metarateData
     * @param GetQuoteDataInterface $getQuoteData
     * @param SaveQuoteDataInterface $saveQuoteData
     */
    public function __construct(
        MetaRateDataInterface $metarateData,
        GetQuoteDataInterface $getQuoteData,
        SaveQuoteDataInterface $saveQuoteData
    ) {
        $this->metarateData = $metarateData;
        $this->getQuoteData = $getQuoteData;
        $this->saveQuoteData = $saveQuoteData;
    }

    /**
     * @param Shipping $subject
     * @param $result
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return mixed
     */
    public function afterCollect(
        Shipping $subject,
        $result,
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        $address = $shippingAssignment->getShipping()->getAddress();
        $method = $shippingAssignment->getShipping()->getMethod();

        if (!$method) {
            return $result;
        }

        $found = false;

        foreach ($address->getAllShippingRates() as $rate) {
            if ($rate->getCode() === 'calcurates_metarate') {
                $found = true;
                break;
            }
        }

        if (!$found) {
            return $result;
        }

        $quoteData = $this->getQuoteData->get($quote->getId());
        $splitShipmentData = $quoteData->getSplitShipments();
        $productData = $this->metarateData->getProductData();
        $proudctQty = $this->metarateData->getProductQtys();
        if (!$splitShipmentData || !$this->metarateData->getRatesData()) {
            $shippingAddress = $quote->getShippingAddress();
            $total->addTotalAmount($subject->getCode(), $shippingAddress->getShippingAmount());
            $total->addBaseTotalAmount($subject->getCode(), $shippingAddress->getBaseShippingAmount());
            return $result;
        }
        foreach ($this->metarateData->getRatesData() as $origin => $rates) {
            foreach ($rates as $rate) {
                foreach ($splitShipmentData as $key => $splitShipment) {
                    if (isset($splitShipment['method'])
                        && $rate->getMethod() == $splitShipment['method']
                        && $origin == $splitShipment['origin']
                    ) {
                        /** todo: move saving data from this */
                        $total->addTotalAmount($subject->getCode(), $rate->getPrice());
                        $total->addBaseTotalAmount($subject->getCode(), $rate->getCost());
                        $splitShipment['cost'] = $rate->getCost();
                        $splitShipment['price'] = $rate->getPrice();
                        $splitShipment['products'] = $this->getProductSku($quote, $productData[$origin]);
                        if ($proudctQty) {
                            $splitShipment['product_qty'] = $this->getProductSkuQty($quote, $proudctQty[$origin]);
                        }
                        $splitShipment['title'] = $rate->getMethodTitle();
                        $splitShipment['code'] = $this->metarateData->getOriginData($origin)['code'];
                        $splitShipmentData[$key] = $splitShipment;
                    }
                }
            }
        }
        $quoteData->setSplitShipments($splitShipmentData);
        $this->saveQuoteData->save($quoteData);

        return $result;
    }

    /**
     * @param Quote $quote
     * @param array $itemId
     * @return array
     */
    private function getProductSku(Quote $quote, array $itemId): array
    {
        $result = [];
        foreach ($itemId as $id) {
            $item = $quote->getItemById($id);
            $result[] = $item->getSku();
        }

        return $result;
    }

    /**
     * @param Quote $quote
     * @param array $itemData
     * @return array
     */
    private function getProductSkuQty(Quote $quote, array $itemData): array
    {
        $result = [];
        foreach ($itemData as $productItem) {
            foreach ($productItem as $itemId => $qty) {
                $item = $quote->getItemById($itemId);
                if (isset($result[$item->getSku()])) {
                    $result[$item->getSku()] += $qty;
                } else {
                    $result[$item->getSku()] = $qty;
                }
            }
        }

        return $result;
    }
}
