<?php

namespace Calcurates\ModuleMagento\Client;

use Calcurates\ModuleMagento\Model\Carrier;
use Calcurates\ModuleMagento\Model\Config as CalcuratesConfig;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;

class RatesResponseProcessor
{
    /**
     * @var ResultFactory
     */
    private $rateFactory;

    /**
     * @var ErrorFactory
     */
    private $rateErrorFactory;

    /**
     * @var CalcuratesConfig
     */
    private $calcuratesConfig;

    /**
     * @var MethodFactory
     */
    private $rateMethodFactory;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * RatesResponseProcessor constructor.
     * @param ResultFactory $rateFactory
     * @param ErrorFactory $rateErrorFactory
     * @param CalcuratesConfig $calcuratesConfig
     * @param MethodFactory $rateMethodFactory
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        ResultFactory $rateFactory,
        ErrorFactory $rateErrorFactory,
        CalcuratesConfig $calcuratesConfig,
        MethodFactory $rateMethodFactory,
        PriceCurrencyInterface $priceCurrency
    )
    {
        $this->rateFactory = $rateFactory;
        $this->rateErrorFactory = $rateErrorFactory;
        $this->calcuratesConfig = $calcuratesConfig;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Prepare shipping rate result based on response
     *
     * @param array $response
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return Result
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function processResponse($response, $quote)
    {
        $result = $this->rateFactory->create();

        // status only for errors
        $status = $response['status'] ?? null;
        if (!$response || $status) {
            $this->processFailedRate(
                $this->calcuratesConfig->getTitle($quote->getStoreId()),
                $result,
                $this->calcuratesConfig->getErrorMessage($quote->getStoreId())
            );

            return $result;
        }

        foreach ($response as $origin) {
            $this->processFreeShipping($origin['freeShipping'], $result);
            $this->processFlatRates($origin['flatRates'], $result);
            $this->processTableRates($origin['tableRates'], $result);
            $this->processCarriers($origin['carriers'], $result);
            $this->processOrigin($origin['origin'], $quote);
        }

        return $result;
    }

    /**
     * @param string $rateName
     * @param Result $result
     * @param string $message
     */
    public function processFailedRate(string $rateName, Result $result, string $message = '')
    {
        $error = $this->rateErrorFactory->create();
        $error->setCarrier(Carrier::CODE);
        $error->setCarrierTitle($rateName);
        $error->setErrorMessage($message);

        $result->append($error);
    }

    /**
     * @param array $origin
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return void
     */
    private function processOrigin($origin, $quote)
    {
        $quote->setData('calcurates_origin_data', json_encode($origin));
    }

    /**
     * @param array $flatRates
     * @param Result $result
     */
    private function processFlatRates(array $flatRates, Result $result)
    {
        foreach ($flatRates as $responseRate) {
            if (!$responseRate['success']) {
                if ($responseRate['message']) {
                    $this->processFailedRate($responseRate['name'], $result, $responseRate['message']);
                }
                continue;
            }

            $this->processRate(
                'flatRates_' . $responseRate['id'],
                $responseRate,
                $result
            );
        }
    }

    /**
     * @param array $freeShipping
     * @param Result $result
     */
    private function processFreeShipping(array $freeShipping, Result $result)
    {
        foreach ($freeShipping as $responseRate) {
            if (!$responseRate['success']) {
                if ($responseRate['message']) {
                    $this->processFailedRate($responseRate['name'], $result, $responseRate['message']);
                }
                continue;
            }

            $responseRate['rate'] = [
                'cost' => 0,
                'currency' => null,
            ];

            $this->processRate(
                'freeShipping' . $responseRate['id'],
                $responseRate,
                $result
            );
        }
    }

    /**
     * @param array $tableRates
     * @param Result $result
     */
    private function processTableRates(array $tableRates, Result $result)
    {
        foreach ($tableRates as $tableRate) {
            if (!$tableRate['success']) {
                if ($tableRate['message']) {
                    $this->processFailedRate($tableRate['name'], $result, $tableRate['message']);
                }

                continue;
            }

            foreach ($tableRate['methods'] as $responseRate) {
                if (!$responseRate['success']) {
                    if ($responseRate['message']) {
                        $this->processFailedRate($responseRate['name'], $result, $responseRate['message']);
                    }

                    continue;
                }

                $this->processRate(
                    'tableRate_' . $tableRate['id'] . '_' . $responseRate['id'],
                    $responseRate,
                    $result
                );
            }
        }
    }

    /**
     * @param array $carriers
     * @param Result $result
     */
    private function processCarriers(array $carriers, Result $result)
    {
        foreach ($carriers as $carrier) {
            if (!$carrier['success']) {
                if ($carrier['message']) {
                    $this->processFailedRate($carrier['name'], $result, $carrier['message']);
                }

                continue;
            }

            foreach ($carrier['services'] as $responseRate) {
                if (!$responseRate['success']) {
                    if ($responseRate['message']) {
                        $this->processFailedRate($responseRate['name'], $result, $responseRate['message']);
                    }

                    continue;
                }

                $this->processRate(
                    'carrier_' . $carrier['id'] . '_' . $responseRate['id'],
                    $responseRate,
                    $result,
                    $carrier['name']
                );
            }
        }
    }

    /**
     * @param string $methodId
     * @param array $responseRate
     * @param Result $result
     * @param string $carrierTitle
     */
    private function processRate($methodId, array $responseRate, Result $result, $carrierTitle = '')
    {
        $rate = $this->rateMethodFactory->create();
        $baseAmount = $this->priceCurrency->convert(
            $responseRate['rate']['cost'],
            null,
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
        $result->append($rate);
    }
}
