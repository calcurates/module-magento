<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor\SplitCheckout;

use Calcurates\ModuleMagento\Api\Data\InStorePickup\PickupLocationInterface;
use Calcurates\ModuleMagento\Api\Data\InStorePickup\PickupLocationInterfaceFactory;
use Calcurates\ModuleMagento\Client\RateBuilder;
use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Calcurates\ModuleMagento\Client\Response\FailedRateBuilder;
use Calcurates\ModuleMagento\Client\Response\MapLinkRenderer;
use Calcurates\ModuleMagento\Client\Response\ResponseProcessorInterface;
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
use Calcurates\ModuleMagento\Model\InStorePickup\Extractor\PickupLocationDataExtractor;
use Calcurates\ModuleMagento\Model\InStorePickup\PickupLocationPersistor;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
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
     * @var PickupLocationDataExtractor
     */
    private $pickupLocationDataExtractor;

    /**
     * @var PickupLocationPersistor
     */
    private $pickupLocationPersistor;

    /**
     * @var PickupLocationInterfaceFactory
     */
    private $pickupLocationFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var State
     */
    private $appState;

    /**
     * InStorePickupProcessor constructor.
     * @param FailedRateBuilder $failedRateBuilder
     * @param RateBuilder $rateBuilder
     * @param MapLinkRenderer $mapLinkRenderer
     * @param PickupLocationDataExtractor $pickupLocationDataExtractor
     * @param PickupLocationPersistor $pickupLocationPersistor
     * @param PickupLocationInterfaceFactory $pickupLocationFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param State $appState
     */
    public function __construct(
        FailedRateBuilder $failedRateBuilder,
        RateBuilder $rateBuilder,
        MapLinkRenderer $mapLinkRenderer,
        PickupLocationDataExtractor $pickupLocationDataExtractor,
        PickupLocationPersistor $pickupLocationPersistor,
        PickupLocationInterfaceFactory $pickupLocationFactory,
        DataObjectHelper $dataObjectHelper,
        State $appState
    ) {
        $this->failedRateBuilder = $failedRateBuilder;
        $this->rateBuilder = $rateBuilder;
        $this->mapLinkRenderer = $mapLinkRenderer;
        $this->pickupLocationDataExtractor = $pickupLocationDataExtractor;
        $this->pickupLocationPersistor = $pickupLocationPersistor;
        $this->pickupLocationFactory = $pickupLocationFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->appState = $appState;
    }

    /**
     * @param Result $result
     * @param array $response
     * @param CartInterface $quote
     */
    public function process(Result $result, array &$response, CartInterface $quote): void
    {
        $origin = $response['origin'];
        foreach ($response['shippingOptions']['inStorePickups'] as $shippingOption) {
            if (!$shippingOption['success'] && empty($shippingOption['stores'])) {
                if ($shippingOption['message']) {
                    $failedRate = $this->failedRateBuilder->build(
                        $shippingOption['displayName'] ?? $shippingOption['name'],
                        '',
                        $shippingOption['message'],
                        $shippingOption['priority']
                    );
                    $result->append($failedRate);
                }

                continue;
            }

            foreach ($shippingOption['stores'] as $store) {
                $store['priority'] = $shippingOption['priority'] + $store['priority'] * 0.001;
                $store['imageUri'] = $store['imageUri'] ?: $shippingOption['imageUri'];
                if ($this->appState->getAreaCode() === Area::AREA_ADMINHTML) {
                    $store['displayName'] = $store['name']
                        . (!empty($store['displayName']) ? " ({$store['displayName']})" : '');
                } else {
                    $store['displayName'] = $store['displayName'] ?? $store['name'];
                }
                if (!$store['success']) {
                    if ($store['message']) {
                        $failedRate = $this->failedRateBuilder->build(
                            $shippingOption['displayName'] ?? $shippingOption['name'],
                            $store['displayName'],
                            $store['message'],
                            $shippingOption['priority']
                        );
                        $result->append($failedRate);
                    }

                    continue;
                }

                $rates = $this->rateBuilder->build(
                    ShippingMethodManager::IN_STORE_PICKUP . '_' . $shippingOption['id'] . '_' . $store['id'],
                    $store,
                    $shippingOption['displayName'] ?? $shippingOption['name']
                );

                foreach ($rates as $rate) {
                    $rate->setData(
                        RatesResponseProcessor::CALCURATES_MAP_LINK,
                        $this->mapLinkRenderer->render($origin)
                    );
                    $result->append($rate);
                }

                $pickupLocationData = $this->pickupLocationDataExtractor->extract($origin);

                $pickupLocation = $this->pickupLocationFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $pickupLocation,
                    $pickupLocationData,
                    PickupLocationInterface::class
                );
                $pickupLocation->setShippingOptionId((int) $store['id']);
                $this->pickupLocationPersistor->save($pickupLocation);
            }
        }
    }
}
