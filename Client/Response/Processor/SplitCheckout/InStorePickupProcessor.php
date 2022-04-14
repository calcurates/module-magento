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
     * InStorePickupProcessor constructor.
     * @param FailedRateBuilder $failedRateBuilder
     * @param RateBuilder $rateBuilder
     * @param MapLinkRenderer $mapLinkRenderer
     * @param PickupLocationDataExtractor $pickupLocationDataExtractor
     * @param PickupLocationPersistor $pickupLocationPersistor
     * @param PickupLocationInterfaceFactory $pickupLocationFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        FailedRateBuilder $failedRateBuilder,
        RateBuilder $rateBuilder,
        MapLinkRenderer $mapLinkRenderer,
        PickupLocationDataExtractor $pickupLocationDataExtractor,
        PickupLocationPersistor $pickupLocationPersistor,
        PickupLocationInterfaceFactory $pickupLocationFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->failedRateBuilder = $failedRateBuilder;
        $this->rateBuilder = $rateBuilder;
        $this->mapLinkRenderer = $mapLinkRenderer;
        $this->pickupLocationDataExtractor = $pickupLocationDataExtractor;
        $this->pickupLocationPersistor = $pickupLocationPersistor;
        $this->pickupLocationFactory = $pickupLocationFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param Result $result
     * @param array $response
     * @param CartInterface $quote
     */
    public function process(Result $result, array &$response, CartInterface $quote): void
    {
        /** @todo */
//    return;
        $origin = $response['origin'];
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
                    $rate->setData(RatesResponseProcessor::CALCURATES_MAP_LINK, $this->mapLinkRenderer->render($origin));
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
