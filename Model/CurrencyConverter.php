<?php

namespace Calcurates\ModuleMagento\Model;

use Magento\Directory\Model\Currency;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;

class CurrencyConverter
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * CurrencyConverter constructor.
     * @param PriceCurrencyInterface $priceCurrency
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(PriceCurrencyInterface $priceCurrency, StoreManagerInterface $storeManager)
    {
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
    }

    /**
     * @param float $amount
     * @param string $fromCurrencyCode
     * @return float
     */
    public function convertToBase($amount, $fromCurrencyCode)
    {
        $baseAmount = $amount;

        if (!$fromCurrencyCode) {
            return $baseAmount;
        }

        try {
            /** @var Currency $baseCurrency */
            $baseCurrency = $this->storeManager->getStore()->getBaseCurrency();
            $baseCurrencyCode = $baseCurrency->getCurrencyCode();
            if ($baseCurrencyCode !== $fromCurrencyCode) {
                /** @var Currency $currency */
                $currency = $this->priceCurrency->getCurrency(null, $fromCurrencyCode);
                $rate = $currency->getRate($baseCurrencyCode);
                if (!$rate) {
                    $rates = $currency->getRates();
                    $fromCurrencyRate = $baseCurrency->getRate($fromCurrencyCode);
                    $rates[$baseCurrencyCode] = $fromCurrencyRate ? 1 / $fromCurrencyRate : 1;
                    $currency->setRates($rates);
                }

                $baseAmount = $currency->convert($baseAmount, $baseCurrencyCode);
            }
        } catch (\Throwable $e) {
            // do nothing
        }

        return $baseAmount;
    }
}
