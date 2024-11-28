<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\ViewModel;

use Calcurates\ModuleMagento\Api\SalesData\QuoteData\GetQuoteDataInterface;
use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address\Rate;

class QuoteDeliveryDate implements ArgumentInterface
{
    /**
     * @var GetQuoteDataInterface
     */
    private $getQuoteData;

    /**
     * @param GetQuoteDataInterface $getQuoteData
     */
    public function __construct(GetQuoteDataInterface $getQuoteData)
    {
        $this->getQuoteData = $getQuoteData;
    }

    /**
     * Get dates from rate or from saved quote data
     *
     * @param Rate $rate
     * @param CartInterface $quote
     * @return array
     */
    public function getDeliveryDates(Rate $rate, CartInterface $quote): array
    {
        if ($rate->getData(RatesResponseProcessor::CALCURATES_DELIVERY_DATES)) {
            return $rate->getData(RatesResponseProcessor::CALCURATES_DELIVERY_DATES);
        }
        if ($quoteData = $this->getQuoteData->get($quote->getId())) {
            $deliveryDatesData = $quoteData->getDeliveryDates();
            if (!empty($deliveryDatesData[$rate->getCode()]) && is_array($deliveryDatesData[$rate->getCode()])) {
                return $deliveryDatesData[$rate->getCode()];
            }
        }
        return [];
    }
}
