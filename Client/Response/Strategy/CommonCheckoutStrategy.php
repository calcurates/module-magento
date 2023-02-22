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
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
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
     * @param array $apiRequestBody
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     * @throws ApiException
     * @throws LocalizedException
     */
    public function getResponse(array $apiRequestBody, $storeId): array
    {
        return $this->calcuratesClient->getRates($apiRequestBody, $storeId);
    }

    /**
     * @param array $response
     * @param Quote $quote
     * @return Result
     */
    public function processResponse(array $response, Quote $quote): Result
    {
        return $this->ratesResponseProcessor->processResponse($response, $quote);
    }
}
