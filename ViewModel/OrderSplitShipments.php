<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\ViewModel;

use Calcurates\ModuleMagento\Api\Data\OrderDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\OrderData\GetOrderDataInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;

class OrderSplitShipments implements ArgumentInterface
{
    /**
     * @var OrderDataInterface
     */
    private $orderData;

    /**
     * @var OrderInterface
     */
    private $order;

    /**
     * @var GetOrderDataInterface
     */
    private $getOrderData;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param GetOrderDataInterface $getOrderData
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        GetOrderDataInterface $getOrderData,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->getOrderData = $getOrderData;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @return bool
     */
    public function isSplitShipment(): bool
    {
        return $this->order->getShippingMethod() === 'calcurates_metarate' && $this->orderData->getSplitShipments();
    }

    /**
     * @param OrderInterface $order
     * @return void
     */
    public function setOrder(OrderInterface $order): void
    {
        $this->order = $order;
        $this->orderData = $this->getOrderData->get($order->getId());
    }

    /**
     * @return OrderInterface|null
     */
    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    /**
     * @return OrderDataInterface
     */
    public function getOrderData(): OrderDataInterface
    {
        return $this->orderData;
    }

    /**
     * @param float $price
     * @return string
     */
    public function getMethodPrice(float $price): string
    {
        return $this->priceCurrency->convertAndFormat(
            $price,
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getOrder()->getStoreId()
        );
    }
}
