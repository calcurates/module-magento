<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client;

use Calcurates\ModuleMagento\Client\Response\FailedRateBuilder;
use Calcurates\ModuleMagento\Client\Response\ResponseProcessorInterface;
use Calcurates\ModuleMagento\Model\Config as CalcuratesConfig;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;

class RatesResponseProcessor
{
    const CALCURATES_TOOLTIP_MESSAGE = 'calcurates_tooltip';
    const CALCURATES_DELIVERY_DATES = 'calcurates_delivery_dates';
    const CALCURATES_MAP_LINK = 'calcurates_map_link';

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var CalcuratesConfig
     */
    private $calcuratesConfig;

    /**
     * @var ResponseProcessorInterface
     */
    private $responseProcessor;

    /**
     * @var FailedRateBuilder
     */
    private $failedRateBuilder;

    /**
     * RatesResponseProcessor constructor.
     * @param ResultFactory $resultFactory
     * @param CalcuratesConfig $calcuratesConfig
     * @param ResponseProcessorInterface $responseProcessor
     * @param FailedRateBuilder $failedRateBuilder
     */
    public function __construct(
        ResultFactory $resultFactory,
        CalcuratesConfig $calcuratesConfig,
        ResponseProcessorInterface $responseProcessor,
        FailedRateBuilder $failedRateBuilder
    ) {
        $this->resultFactory = $resultFactory;
        $this->calcuratesConfig = $calcuratesConfig;
        $this->responseProcessor = $responseProcessor;
        $this->failedRateBuilder = $failedRateBuilder;
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
        $result = $this->resultFactory->create();

        // status only for errors
        $status = $response['status'] ?? null;
        if (!$response || empty($response['shippingOptions']) || $status) {
            $failedRate = $this->failedRateBuilder->build(
                $this->calcuratesConfig->getTitle($quote->getStoreId()),
                $this->calcuratesConfig->getErrorMessage($quote->getStoreId())
            );
            $result->append($failedRate);

            return $result;
        }

        $this->responseProcessor->process($result, $response, $quote);

        return $result;
    }

    /**
     * @deprecated Use FailedRateBuilder instead of this method
     * @param string $rateName
     * @param Result $result
     * @param string $message
     */
    public function processFailedRate(string $rateName, Result $result, string $message = '')
    {
        $failedRate = $this->failedRateBuilder->build($rateName, $message);
        $result->append($failedRate);
    }
}
