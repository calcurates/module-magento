<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client;

use Calcurates\ModuleMagento\Client\Response\FailedRateBuilder;
use Calcurates\ModuleMagento\Model\Config as CalcuratesConfig;
use Calcurates\ModuleMagento\Model\Data\MetaRateData;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;

class MetaRatesResponseProcessor
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
     * @var FailedRateBuilder
     */
    private $failedRateBuilder;

    /**
     * @var MetaRateData
     */
    private $metaRateData;

    /**
     * @var RatesResponseProcessor
     */
    private $ratesResponseProcessor;

    /**
     * @var MetaRateBuilder
     */
    private $rateBuilder;

    /**
     * RatesResponseProcessor constructor.
     * @param ResultFactory $resultFactory
     * @param CalcuratesConfig $calcuratesConfig
     * @param FailedRateBuilder $failedRateBuilder
     * @param MetaRateData $metaRateData
     * @param RatesResponseProcessor $ratesResponseProcessor
     * @param \Calcurates\ModuleMagento\Client\MetaRateBuilder $metaRateBuilder
     */
    public function __construct(
        ResultFactory $resultFactory,
        CalcuratesConfig $calcuratesConfig,
        FailedRateBuilder $failedRateBuilder,
        MetaRateData $metaRateData,
        RatesResponseProcessor $ratesResponseProcessor,
        MetaRateBuilder $metaRateBuilder
    ) {
        $this->resultFactory = $resultFactory;
        $this->calcuratesConfig = $calcuratesConfig;
        $this->failedRateBuilder = $failedRateBuilder;
        $this->metaRateData = $metaRateData;
        $this->ratesResponseProcessor = $ratesResponseProcessor;
        $this->rateBuilder = $metaRateBuilder;
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
        $splitCheckout = true;
        if (!$response || (empty($response['shippingOptions']) && !$splitCheckout) || $status) {
            $failedRate = $this->failedRateBuilder->build(
                $this->calcuratesConfig->getTitle($quote->getStoreId()),
                $this->calcuratesConfig->getErrorMessage($quote->getStoreId())
            );
            $result->append($failedRate);

            return $result;
        }

        $metarate = $this->rateBuilder->build(
            'MetaRate',
            [],
            $this->calcuratesConfig->getSplitCheckoutTitle($quote->getStoreId()),
            $this->calcuratesConfig->getCarrierTitle($quote->getStoreId())
        );
        $result->append($metarate);
        foreach ($response['origins'] as $origin) {
            $childRates = $this->ratesResponseProcessor->processResponse($origin, $quote);
            $this->metaRateData->setRatesData($origin['origin']['id'], $childRates->getAllRates());
        }

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
