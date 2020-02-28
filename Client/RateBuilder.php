<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client;

use Calcurates\ModuleMagento\Model\Carrier;
use Calcurates\ModuleMagento\Model\CurrencyConverter;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;

class RateBuilder
{
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
     */
    public function __construct(MethodFactory $rateMethodFactory, CurrencyConverter $currencyConverter)
    {
        $this->rateMethodFactory = $rateMethodFactory;
        $this->currencyConverter = $currencyConverter;
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
        $rate->setCost($baseAmount);
        $rate->setPrice($baseAmount);

        return $rate;
    }
}
