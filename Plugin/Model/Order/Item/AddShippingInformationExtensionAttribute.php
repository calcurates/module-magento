<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Model\Order\Item;

use Calcurates\ModuleMagento\Api\SalesData\OrderData\GetOrderDataInterface;
use Calcurates\ModuleMagento\Api\Data\Order\Item\ShippingInformationInterfaceFactory;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\Data\OrderItemSearchResultInterface;
use Calcurates\ModuleMagento\Api\ConfigProviderInterface;

class AddShippingInformationExtensionAttribute
{
    /**
     * @var GetOrderDataInterface
     */
    private $getOrderData;

    /**
     * @var ShippingInformationInterfaceFactory
     */
    private $additionalInformationFactory;

    /**
     * @var OrderItemExtensionFactory
     */
    private $extensionFactory;

    /**
     * @var array
     */
    private $orderData = [];

    /**
     * @var ConfigProviderInterface
     */
    private $configProvider;

    /**
     * AddShippingInformationExtensionAttribute constructor.
     * @param GetOrderDataInterface $getOrderData
     * @param ShippingInformationInterfaceFactory $additionalInformationFactory
     * @param OrderItemExtensionFactory $extensionFactory
     * @param ConfigProviderInterface $configProvider
     */
    public function __construct(
        GetOrderDataInterface $getOrderData,
        ShippingInformationInterfaceFactory $additionalInformationFactory,
        OrderItemExtensionFactory $extensionFactory,
        ConfigProviderInterface $configProvider
    ) {
        $this->configProvider = $configProvider;
        $this->extensionFactory = $extensionFactory;
        $this->getOrderData = $getOrderData;
        $this->additionalInformationFactory = $additionalInformationFactory;
    }

    /**
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param OrderItemInterface $orderItem
     * @return OrderItemInterface
     */
    public function afterGet(
        OrderItemRepositoryInterface $orderItemRepository,
        OrderItemInterface $orderItem
    ): OrderItemInterface {
        if (!$this->configProvider->isUniqueOrderItemSource()) {
            return $orderItem;
        }
        $orderId = $orderItem->getOrderId();
        if (!isset($this->orderData[$orderId])) {
            $this->orderData[$orderId] = $this->getOrderData->get($orderId);
        }
        $orderData = $this->orderData[$orderId];
        if ($orderData && $orderData->getSplitShipments()) {
            $shippingAdditionalInfo = null;
            foreach ($orderData->getSplitShipments() as $splitShipment) {
                $productInfo = $splitShipment;
                $productInfo['method_price'] = $productInfo['price'];
                if (isset($splitShipment['product_qty']) && is_array($splitShipment['product_qty'])) {
                    foreach ($splitShipment['product_qty'] as $sku => $qty) {
                        if ($orderItem->getSku() === $sku) {
                            $productInfo['qty'] = $qty;
                        }
                    }
                } elseif (isset($splitShipment['products']) && is_array($splitShipment['products'])) {
                    foreach ($splitShipment['products'] as $sku) {
                        if ($orderItem->getSku() === $sku) {
                            $productInfo['qty'] = 1;

                        }
                    }
                }
                if (isset($productInfo['qty']) && $productInfo['qty']) {
                    $shippingAdditionalInfo = $this->additionalInformationFactory
                        ->create(['data' => $productInfo]);
                }
            }
            if ($shippingAdditionalInfo) {
                $extensionAttributes = $orderItem->getExtensionAttributes() ?? $this->extensionFactory->create();
                $extensionAttributes->setCalcuratesShippingInformation($shippingAdditionalInfo);
                $orderItem->setExtensionAttributes($extensionAttributes);
            }
        }
        return $orderItem;
    }

    /**
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param OrderItemSearchResultInterface $searchResult
     * @return OrderItemSearchResultInterface
     */
    public function afterGetList(
        OrderItemRepositoryInterface $orderItemRepository,
        OrderItemSearchResultInterface $searchResult
    ): OrderItemSearchResultInterface {
        foreach ($searchResult->getItems() as $item) {
            $this->afterGet($orderItemRepository, $item);
        }
        return $searchResult;
    }
}
