<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client;

use Calcurates\ModuleMagento\Api\Data\QuoteDataInterfaceFactory;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\GetQuoteDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\SaveQuoteDataInterface;
use Calcurates\ModuleMagento\Client\Response\FailedRateBuilder;
use Calcurates\ModuleMagento\Client\Response\ResponseProcessorInterface;
use Calcurates\ModuleMagento\Model\Config as CalcuratesConfig;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;

class RatesResponseProcessor
{
    public const CALCURATES_TOOLTIP_MESSAGE = 'calcurates_tooltip';
    public const CALCURATES_DELIVERY_DATES = 'calcurates_delivery_dates';
    public const CALCURATES_MAP_LINK = 'calcurates_map_link';
    public const CALCURATES_IMAGE_URL = 'calcurates_image_url';

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
     * @var GetQuoteDataInterface
     */
    private $getQuoteData;

    /**
     * @var SaveQuoteDataInterface
     */
    private $saveQuoteData;

    /**
     * @var QuoteDataInterfaceFactory
     */
    private $quoteDataFactory;

    /**
     * RatesResponseProcessor constructor.
     * @param ResultFactory $resultFactory
     * @param CalcuratesConfig $calcuratesConfig
     * @param ResponseProcessorInterface $responseProcessor
     * @param FailedRateBuilder $failedRateBuilder
     * @param GetQuoteDataInterface $getQuoteData
     * @param SaveQuoteDataInterface $saveQuoteData
     * @param QuoteDataInterfaceFactory $quoteDataFactory
     */
    public function __construct(
        ResultFactory $resultFactory,
        CalcuratesConfig $calcuratesConfig,
        ResponseProcessorInterface $responseProcessor,
        FailedRateBuilder $failedRateBuilder,
        GetQuoteDataInterface $getQuoteData,
        SaveQuoteDataInterface $saveQuoteData,
        QuoteDataInterfaceFactory $quoteDataFactory
    ) {
        $this->resultFactory = $resultFactory;
        $this->calcuratesConfig = $calcuratesConfig;
        $this->responseProcessor = $responseProcessor;
        $this->failedRateBuilder = $failedRateBuilder;
        $this->getQuoteData = $getQuoteData;
        $this->saveQuoteData = $saveQuoteData;
        $this->quoteDataFactory = $quoteDataFactory;
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
        if ($response && isset($response['shippingOptions']) && is_array($response['shippingOptions'])) {
            $shippingOptionsExist = false;
            foreach ($response['shippingOptions'] as $ratesGroupName => $ratesData) {
                if ($ratesData) {
                    $shippingOptionsExist = true;
                }
            }
        }
        $failedRate = $this->failedRateBuilder->build(
            $this->calcuratesConfig->getTitle($quote->getStoreId()),
            $this->calcuratesConfig->getErrorMessage($quote->getStoreId())
        );
        if (!$response
            || empty($response['shippingOptions'])
            || $status
            || (isset($shippingOptionsExist) && !$shippingOptionsExist)
        ) {
            $result->append($failedRate);
            return $result;
        }

        $this->responseProcessor->process($result, $response, $quote);

        // @TODO: temporary fix, need refactoring
        $deliveryDates = [];
        foreach ($result->getAllRates() as $rate) {
            $rateDeliveryDates = $rate->getData(self::CALCURATES_DELIVERY_DATES);
            if ($rateDeliveryDates) {
                $deliveryDates[$rate->getCarrier() . '_' . $rate->getMethod()] = $rateDeliveryDates;
            }
        }

        if (empty($result->getAllRates())) {
            $result->append($failedRate);
            return $result;
        }

        if (null === $quote->getId()) {
            return $result;
        }

        $quoteData = $this->getQuoteData->get((int)$quote->getId());

        if (!$quoteData) {
            $quoteData = $this->quoteDataFactory->create();
            $quoteData->setQuoteId((int)$quote->getId());
        }

        $quoteData->setDeliveryDates($deliveryDates);
        $this->saveQuoteData->save($quoteData);

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
