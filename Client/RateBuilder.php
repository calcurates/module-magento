<?php
declare(strict_types=1);
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client;

use Calcurates\ModuleMagento\Model\Carrier;
use Calcurates\ModuleMagento\Model\Config;
use Calcurates\ModuleMagento\Model\Config\Source\RateTaxDisplaySource;
use Calcurates\ModuleMagento\Model\CurrencyConverter;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;

class RateBuilder
{
    const METHOD_WITH_TAX_SUFFIX = '_tax';

    /**
     * @var Config
     */
    private $calcuratesConfig;

    /**
     * @var MethodFactory
     */
    private $rateMethodFactory;

    /**
     * @var CurrencyConverter
     */
    private $currencyConverter;

    /**
     * RateBuilder constructor.
     * @param MethodFactory $rateMethodFactory
     * @param CurrencyConverter $currencyConverter
     * @param Config $calcuratesConfig
     */
    public function __construct(
        MethodFactory $rateMethodFactory,
        CurrencyConverter $currencyConverter,
        Config $calcuratesConfig
    ) {
        $this->rateMethodFactory = $rateMethodFactory;
        $this->currencyConverter = $currencyConverter;
        $this->calcuratesConfig = $calcuratesConfig;
    }

    /**
     * @param string $methodId
     * @param array $responseRate
     * @param string $carrierTitle
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method[]
     */
    public function build(string $methodId, array $responseRate, $carrierTitle = ''): array
    {
        $displayRatesType = $this->calcuratesConfig->getRatesTaxDisplayType();
        $tax = $responseRate['rate']['tax'] ?? null;
        if ($displayRatesType === RateTaxDisplaySource::BOTH && $tax) {
            $responseRateWithTax = $responseRate;
            $responseRateWithTax['rate']['cost'] += $tax;
            $responseRateWithTax['name'] .= __(' - duties & tax included');
            $rateWithTax = $this->createRate(
                $methodId . self::METHOD_WITH_TAX_SUFFIX,
                $responseRateWithTax,
                $carrierTitle
            );
            $responseRateWithoutTax = $responseRate;
            $responseRateWithoutTax['name'] .= __(' - without duties & tax');
            $rateWithoutTax = $this->createRate($methodId, $responseRateWithoutTax, $carrierTitle);

            return [$rateWithTax, $rateWithoutTax];
        }

        if ($displayRatesType === RateTaxDisplaySource::TAX_INCLUDED && $tax) {
            $responseRate['rate']['cost'] += $tax;
            $responseRate['name'] .= __(' - duties & tax included');
        }

        return [$this->createRate($methodId, $responseRate, $carrierTitle)];
    }

    /**
     * @param string $methodId
     * @param array $responseRate
     * @param string $carrierTitle
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    private function createRate(string $methodId, array $responseRate, $carrierTitle = '')
    {
        $rate = $this->rateMethodFactory->create();
        $baseAmount = $this->currencyConverter->convertToBase(
            $responseRate['rate']['cost'],
            $responseRate['rate']['currency']
        );
        $rate->setCarrier(Carrier::CODE);
        $rate->setMethod($methodId);
        $rate->setMethodTitle($responseRate['name']);
        $rate->setCarrierTitle($carrierTitle);
        $rate->setInfoMessageEnabled((bool)$responseRate['message']);
        $rate->setInfoMessage($responseRate['message']);
        $rate->setPriority($responseRate['priority']);
        $rate->setData(RatesResponseProcessor::CALCURATES_IMAGE_URL, $responseRate['imageUri']);
        $rate->setCost($baseAmount);
        $rate->setPrice($baseAmount);
        $rate->setData(RatesResponseProcessor::CALCURATES_TOOLTIP_MESSAGE, $responseRate['message']);
        if (!empty($responseRate['rate']['estimatedDeliveryDate'])) {
            $rate->setData(
                RatesResponseProcessor::CALCURATES_DELIVERY_DATES,
                $responseRate['rate']['estimatedDeliveryDate']
            );
        }

        return $rate;
    }
}
