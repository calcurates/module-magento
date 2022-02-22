<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Strategy;

use Calcurates\ModuleMagento\Api\Client\CalcuratesClientInterface;
use Calcurates\ModuleMagento\Client\Http\ApiException;
use Calcurates\ModuleMagento\Client\MetaRatesResponseProcessor;
use Calcurates\ModuleMagento\Client\Response\Strategy\RatesStrategy;

class SplitCheckoutStrategy implements RatesStrategy
{
    /**
     * @var CalcuratesClientInterface
     */
    private $calcuratesClient;

    /**
     * @var MetaRatesResponseProcessor
     */
    private $ratesResponseProcessor;

    /**
     * @param CalcuratesClientInterface $calcuratesClient
     * @param MetaRatesResponseProcessor $ratesResponseProcessor
     */
    public function __construct(
        CalcuratesClientInterface $calcuratesClient,
        MetaRatesResponseProcessor $ratesResponseProcessor
    ) {
        $this->calcuratesClient = $calcuratesClient;
        $this->ratesResponseProcessor = $ratesResponseProcessor;
    }

    /**
     * @param $apiRequestBody
     * @param $storeId
     * @return array|mixed
     * @throws ApiException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getResponse($apiRequestBody, $storeId)
    {
        return $this->calcuratesClient->getRatesSplitCheckout($apiRequestBody, $storeId);
    }

    /**
     * @param $response
     * @param $quote
     * @return \Magento\Shipping\Model\Rate\Result|mixed
     */
    public function processResponse($response, $quote)
    {
        return $this->ratesResponseProcessor->processResponse($response, $quote);
    }
}
