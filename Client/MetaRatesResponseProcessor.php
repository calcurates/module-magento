<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client;

use Calcurates\ModuleMagento\Client\Response\FailedRateBuilder;
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
use Calcurates\ModuleMagento\Model\Config as CalcuratesConfig;
use Calcurates\ModuleMagento\Model\Data\MetaRateData;
use Magento\Quote\Model\Quote;
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
     * @param MetaRateBuilder $metaRateBuilder
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
     * @param Quote $quote
     *
     * @return Result
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function processResponse(array $response, Quote $quote): Result
    {
        $result = $this->resultFactory->create();

        // status only for errors
        $status = $response['status'] ?? null;
        if ($response && isset($response['origins']) && is_array($response['origins'])) {
            $shippingOptionsExist = false;
            foreach ($response['origins'] as $origin) {
                foreach ($origin['shippingOptions'] ?? [] as $ratesGroupName => $ratesData) {
                    if ($ratesData) {
                        $shippingOptionsExist = true;
                    }
                }
            }
        }
        if (!$response
            || $status
            || (isset($shippingOptionsExist) && !$shippingOptionsExist)
        ) {
            $failedRate = $this->failedRateBuilder->build(
                $this->calcuratesConfig->getTitle($quote->getStoreId()),
                '',
                $this->calcuratesConfig->getErrorMessage($quote->getStoreId())
            );
            $result->append($failedRate);

            return $result;
        }

        $metarate = $this->rateBuilder->build(
            ShippingMethodManager::META_RATE,
            $this->calcuratesConfig->getSplitCheckoutTitle($quote->getStoreId()),
            $this->calcuratesConfig->getCarrierTitle($quote->getStoreId())
        );
        $result->append($metarate);
        $sortedOrigins = $response['origins'];
        usort($sortedOrigins, function ($origin1, $origin2) {
            $stringLength =  strlen($origin1['origin']['syncedTargetOriginCode'])
            >= strlen($origin2['origin']['syncedTargetOriginCode'])
                ? strlen($origin1['origin']['syncedTargetOriginCode'])
                : strlen($origin2['origin']['syncedTargetOriginCode']);
            return strncasecmp(
                $origin1['origin']['syncedTargetOriginCode'],
                $origin2['origin']['syncedTargetOriginCode'],
                $stringLength
            );
        });
        foreach ($sortedOrigins as $origin) {
            $childRates = $this->ratesResponseProcessor->processResponse($origin, $quote);
            $this->metaRateData->setRatesData($origin['origin']['id'], $childRates->getAllRates());
        }

        return $result;
    }
}
