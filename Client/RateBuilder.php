<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client;

use Calcurates\ModuleMagento\Model\Carrier;
use Calcurates\ModuleMagento\Model\Config;
use Calcurates\ModuleMagento\Model\CurrencyConverter;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;

class RateBuilder
{
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
    public function __construct(MethodFactory $rateMethodFactory, CurrencyConverter $currencyConverter, Config $calcuratesConfig)
    {
        $this->rateMethodFactory = $rateMethodFactory;
        $this->currencyConverter = $currencyConverter;
        $this->calcuratesConfig = $calcuratesConfig;
    }

    /**
     * @param string $methodId
     * @param array $responseRate
     * @param string $carrierTitle
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    public function build($methodId, array $responseRate, $carrierTitle = '')
    {
        $rate = $this->rateMethodFactory->create();

        $cost = $responseRate['rate']['cost'];
        if ($this->calcuratesConfig->isDisplayRatesWithTax() && isset($responseRate['rate']['tax'])) {
            $cost += $responseRate['rate']['tax'];
        }

        $baseAmount = $this->currencyConverter->convertToBase(
            $cost,
            $responseRate['rate']['currency']
        );
        $rate->setCarrier(Carrier::CODE);
        $rate->setMethod($methodId);
        $rate->setMethodTitle($responseRate['name']);
        $rate->setCarrierTitle($carrierTitle);
        $rate->setInfoMessageEnabled((bool)$responseRate['message']);
        $rate->setInfoMessage($responseRate['message']);
        $rate->setCost($baseAmount);
        $rate->setPrice($baseAmount);

        return $rate;
    }
}
