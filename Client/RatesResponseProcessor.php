<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client;

use Calcurates\ModuleMagento\Model\Carrier;
use Calcurates\ModuleMagento\Model\Config as CalcuratesConfig;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Item;
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
     * @var RateBuilder
     */
    private $rateBuilder;

    /**
     * RatesResponseProcessor constructor.
     * @param ResultFactory $rateFactory
     * @param ErrorFactory $rateErrorFactory
     * @param CalcuratesConfig $calcuratesConfig
     * @param RateBuilder $rateBuilder
     */
    public function __construct(
        ResultFactory $rateFactory,
        ErrorFactory $rateErrorFactory,
        CalcuratesConfig $calcuratesConfig,
        RateBuilder $rateBuilder
    )
    {
        $this->rateFactory = $rateFactory;
        $this->rateErrorFactory = $rateErrorFactory;
        $this->calcuratesConfig = $calcuratesConfig;
        $this->rateBuilder = $rateBuilder;
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
        if (!$response || empty($response['shippingOptions']) || $status) {
            $this->processFailedRate(
                $this->calcuratesConfig->getTitle($quote->getStoreId()),
                $result,
                $this->calcuratesConfig->getErrorMessage($quote->getStoreId())
            );

            return $result;
        }

        $this->processOrigins($response['origins'], $quote);
        $shippingOptions = $response['shippingOptions'];
        $this->processFreeShipping($shippingOptions['freeShipping'], $result);
        $this->processFlatRates($shippingOptions['flatRates'], $result);
        $this->processTableRates($shippingOptions['tableRates'], $result);
        $this->processCarriers($shippingOptions['carriers'], $result);

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

            $rate = $this->rateBuilder->build(
                'flatRates_' . $responseRate['id'],
                $responseRate
            );
            $result->append($rate);
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

            $rate = $this->rateBuilder->build(
                'freeShipping' . $responseRate['id'],
                $responseRate
            );
            $result->append($rate);
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

                $rate = $this->rateBuilder->build(
                    'tableRate_' . $tableRate['id'] . '_' . $responseRate['id'],
                    $responseRate
                );
                $result->append($rate);
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

                $rate = $this->rateBuilder->build(
                    'carrier_' . $carrier['id'] . '_' . $responseRate['id'],
                    $responseRate,
                    $carrier['name']
                );
                $result->append($rate);
            }
        }
    }

    /**
     * @param array $origins
     * @param \Magento\Quote\Model\Quote $quote
     */
    private function processOrigins(array $origins, $quote)
    {
        $quoteItemIdToSourceCode = [];
        foreach ($origins as $origin) {
            $sourceCode = $origin['origin']['targetValue']['targetId'] ?? null;
            if ($sourceCode === null) {
                continue;
            }
            foreach ($origin['products'] as $product) {
                $quoteItemId = $product['quoteItemId'];
                $quoteItemIdToSourceCode[$quoteItemId] = $sourceCode;
            }
        }

        foreach ($quote->getAllItems() as $quoteItem) {
            /** @var Item $quoteItem */
            if (array_key_exists($quoteItem->getId(), $quoteItemIdToSourceCode)) {
                $quoteItem->setData('calcurates_source_code', $quoteItemIdToSourceCode[$quoteItem->getId()]);
            }
        }
    }
}
