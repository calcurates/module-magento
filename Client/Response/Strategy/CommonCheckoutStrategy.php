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
use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Calcurates\ModuleMagento\Client\Response\Strategy\RatesStrategyInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Shipping\Model\Rate\Result;

class CommonCheckoutStrategy implements RatesStrategyInterface
{
    /**
     * @var CalcuratesClientInterface
     */
    private $calcuratesClient;

    /**
     * @var RatesResponseProcessor
     */
    private $ratesResponseProcessor;

    /**
     * @param CalcuratesClientInterface $calcuratesClient
     * @param RatesResponseProcessor $ratesResponseProcessor
     */
    public function __construct(
        CalcuratesClientInterface $calcuratesClient,
        RatesResponseProcessor $ratesResponseProcessor
    ) {
        $this->calcuratesClient = $calcuratesClient;
        $this->ratesResponseProcessor = $ratesResponseProcessor;
    }

    /**
     * @param $apiRequestBody
     * @param $storeId
     * @return array
     * @throws ApiException
     * @throws LocalizedException
     */
    public function getResponse($apiRequestBody, $storeId): array
    {
        return $this->calcuratesClient->getRates($apiRequestBody, $storeId);
    }

    /**
     * @param $response
     * @param $quote
     * @return Result
     */
    public function processResponse($response, $quote): Result
    {
        return $this->ratesResponseProcessor->processResponse($response, $quote);
    }
}