<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor;

use Calcurates\ModuleMagento\Client\RateBuilder;
use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Calcurates\ModuleMagento\Client\Response\FailedRateBuilder;
use Calcurates\ModuleMagento\Client\Response\MapLinkRenderer;
use Calcurates\ModuleMagento\Client\Response\ResponseProcessorInterface;
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Shipping\Model\Rate\Result;

class InStorePickupProcessor implements ResponseProcessorInterface
{
    /**
     * @var FailedRateBuilder
     */
    private $failedRateBuilder;

    /**
     * @var RateBuilder
     */
    private $rateBuilder;

    /**
     * @var MapLinkRenderer
     */
    private $mapLinkRenderer;

    /**
     * InStorePickupProcessor constructor.
     * @param FailedRateBuilder $failedRateBuilder
     * @param RateBuilder $rateBuilder
     * @param MapLinkRenderer $mapLinkRenderer
     */
    public function __construct(
        FailedRateBuilder $failedRateBuilder,
        RateBuilder $rateBuilder,
        MapLinkRenderer $mapLinkRenderer
    ) {
        $this->failedRateBuilder = $failedRateBuilder;
        $this->rateBuilder = $rateBuilder;
        $this->mapLinkRenderer = $mapLinkRenderer;
    }

    /**
     * @param Result $result
     * @param array $response
     * @param CartInterface $quote
     */
    public function process(Result $result, array $response, CartInterface $quote): void
    {
        foreach ($response['shippingOptions']['inStorePickups'] as $shippingOption) {
            if (!$shippingOption['success']) {
                if ($shippingOption['message']) {
                    $failedRate = $this->failedRateBuilder->build(
                        $shippingOption['name'],
                        $shippingOption['message'],
                        $shippingOption['priority']
                    );
                    $result->append($failedRate);
                }

                continue;
            }

            foreach ($shippingOption['stores'] as $store) {
                if (!$store['success']) {
                    if ($store['message']) {
                        $failedRate = $this->failedRateBuilder->build(
                            $store['name'],
                            $store['message'],
                            $shippingOption['priority']
                        );
                        $result->append($failedRate);
                    }

                    continue;
                }

                $store['priority'] = $shippingOption['priority'];
                $store['imageUri'] = $store['imageUri'] ?: $shippingOption['imageUri'];
                $rates = $this->rateBuilder->build(
                    ShippingMethodManager::IN_STORE_PICKUP . '_' . $shippingOption['id'] . '_' . $store['id'],
                    $store,
                    $shippingOption['name']
                );

                foreach ($rates as $rate) {
                    $rate->setData(RatesResponseProcessor::CALCURATES_MAP_LINK, $this->mapLinkRenderer->render($store['origin']));
                    $result->append($rate);
                }
            }
        }
    }
}
