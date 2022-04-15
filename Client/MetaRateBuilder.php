<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client;

use Calcurates\ModuleMagento\Model\Carrier;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;

class MetaRateBuilder
{
    /**
     * @var MethodFactory
     */
    private $rateMethodFactory;

    /**
     * RateBuilder constructor.
     * @param MethodFactory $rateMethodFactory
     */
    public function __construct(
        MethodFactory $rateMethodFactory
    ) {
        $this->rateMethodFactory = $rateMethodFactory;
    }

    /**
     * @param string $methodId
     * @param array $responseRate
     * @param $carrierTitle
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    public function build(string $methodId, array $responseRate, $carrierTitle = '')
    {
        return $this->createRate($methodId, $responseRate, $carrierTitle);
    }

    /**
     * @param string $methodId
     * @param array $responseRate
     * @param $carrierTitle
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    private function createRate(string $methodId, array $responseRate, $carrierTitle = '')
    {
        $rate = $this->rateMethodFactory->create();
        $baseAmount = 0;
        $rate->setCarrier(Carrier::CODE);
        $rate->setMethod($methodId);
        $rate->setCarrierTitle($carrierTitle);
        $rate->setInfoMessageEnabled(0);
        $rate->setInfoMessage('');
        $rate->setPriority('');
        $rate->setCost($baseAmount);
        $rate->setPrice($baseAmount);

        return $rate;
    }
}
