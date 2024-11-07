<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\ViewModel;

use Calcurates\ModuleMagento\Api\Data\MetaRateDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\GetQuoteDataInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Item;
use Magento\Tax\Helper\Data;
use Magento\Quote\Model\Quote\Address\RateResult\Error;

class MetaRate implements ArgumentInterface
{
    /**
     * @var MetaRateDataInterface
     */
    private $metaRateData;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var GetQuoteDataInterface
     */
    private $getQuoteData;

    /**
     * @var array
     */
    private $savedMethods = [];

    /**
     * @var Data
     */
    private $taxHelper;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param MetaRateDataInterface $metaRateData
     * @param Session $checkoutSession
     * @param GetQuoteDataInterface $getQuoteData
     * @param Data $taxHelper
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        MetaRateDataInterface $metaRateData,
        Session $checkoutSession,
        GetQuoteDataInterface $getQuoteData,
        Data $taxHelper,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->metaRateData = $metaRateData;
        $this->checkoutSession = $checkoutSession;
        $this->getQuoteData = $getQuoteData;
        $this->priceCurrency = $priceCurrency;
        $this->taxHelper = $taxHelper;
    }

    /**
     * @return MetaRateDataInterface
     */
    public function getMetaRateData(): MetaRateDataInterface
    {
        return $this->metaRateData;
    }

    /**
     * @param int $id
     * @return Item|null
     */
    public function getQuoteItemById(int $id): ?Item
    {
        try {
            return $this->checkoutSession->getQuote()->getItemById($id);
        } catch (NoSuchEntityException|LocalizedException $e) {
            return null;
        }
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getSkusListByItemIds(array $ids): array
    {
        $skus = [];
        foreach ($ids as $id) {
            $item = $this->getQuoteItemById($id);
            if ($item) {
                $skus[] = $item->getSku();
            }
        }
        return $skus;
    }

    /**
     * @param array $itemData
     * @return array
     */
    public function getProductSkuQty(array $itemData): array
    {
        $result = [];
        foreach ($itemData as $productItem) {
            foreach ($productItem as $itemId => $qty) {
                $item = $this->getQuoteItemById($itemId);
                if ($item) {
                    $result[$item->getSku()] = $item->getSku() . ' (' . $qty . ')';
                }
            }
        }
        return $result;
    }

    /**
     * @param int $originId
     * @param string $method
     * @return bool
     */
    public function isSavedMethod(int $originId, string $method): bool
    {
        if (!$this->savedMethods) {
            $quoteData = $this->getQuoteData->get(
                $this->checkoutSession->getQuoteId()
            );
            foreach ($quoteData->getSplitShipments() ?? [] as $splitShipment) {
                $this->savedMethods[$splitShipment['origin']] = [
                    'method' => $splitShipment['method']
                ];
            }
        }
        if ($this->savedMethods && isset($this->savedMethods[$originId])) {
            return $this->savedMethods[$originId]['method'] === $method;
        }
        return false;
    }

    /**
     * @param Method $rate
     * @param string $format
     * @param string $inclTaxFormat
     * @return string
     */
    public function renderShippingRateOption(
        Method $rate,
        string $format = '%s - %s%s',
        string $inclTaxFormat = ' (%s %s)'
    ): string {
        $renderedInclTax = '';
        if ($rate->getErrorMessage()) {
            $price = $rate->getErrorMessage();
        } else {
            $price = $this->getShippingPrice(
                $rate->getPrice(),
                $this->taxHelper->displayShippingPriceIncludingTax()
            );

            $incl = $this->getShippingPrice($rate->getPrice(), true);
            if ($incl != $price && $this->taxHelper->displayShippingBothPrices()) {
                $renderedInclTax = sprintf($inclTaxFormat, __('Incl. Tax'), $incl);
            }
        }
        return sprintf($format, $rate->getMethodTitle(), $price, $renderedInclTax);
    }

    /**
     * @param Error $rate
     * @param string $format
     * @return string
     */
    public function renderShippingRateErrorOption(
        Error $rate,
        string $format = '%s - %s'
    ): string {
        return sprintf($format, $rate->getMethodTitle(), $rate->getErrorMessage());
    }

    /**
     * @param Error $rate
     * @return string
     */
    public function getErrorRateOptionValue(
        Error $rate
    ): string {
        return sprintf('%s_%s', $rate->getCarrier(), strtolower(trim($rate->getMethodTitle())));
    }

    /**
     * Return formatted shipping price
     *
     * @param float $price
     * @param bool $isInclTax
     * @return string
     */
    private function getShippingPrice(float $price, bool $isInclTax): string
    {
        try {
            return $this->formatPrice(
                $this->taxHelper->getShippingPrice(
                    $price,
                    $isInclTax,
                    $this->checkoutSession->getQuote()->getShippingAddress()
                )
            );
        } catch (NoSuchEntityException|LocalizedException $e) {
            return '';
        }
    }

    /**
     * Format price base on store convert price method
     *
     * @param float $price
     * @return string
     */
    private function formatPrice(float $price): string
    {
        try {
            return $this->priceCurrency->convertAndFormat(
                $price,
                true,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $this->checkoutSession->getQuote()->getStore()
            );
        } catch (NoSuchEntityException|LocalizedException $e) {
            return '';
        }
    }
}
