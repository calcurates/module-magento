<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\CheckoutConverter;

use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class QuoteToOrderConverter
{
    /**
     * @var ConvertPackages
     */
    private $convertPackages;

    /**
     * @var ConvertServicesSources
     */
    private $convertServicesSources;

    /**
     * @var ShippingMethodManager
     */
    private $shippingMethodManager;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        ConvertPackages $convertPackages,
        ConvertServicesSources $convertServicesSources,
        ShippingMethodManager $shippingMethodManager,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->convertPackages = $convertPackages;
        $this->convertServicesSources = $convertServicesSources;
        $this->shippingMethodManager = $shippingMethodManager;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Quote $quote
     * @param Order $order
     */
    public function convert(Quote $quote, Order $order): void
    {
        $carrierData = $this->shippingMethodManager->getCarrierData($order->getShippingMethod(false));

        if (!$carrierData) {
            return;
        }

        $this->convertPackages->convert($quote, $order, $carrierData);
        $this->convertServicesSources->convert($quote, $order);
        $this->orderRepository->save($order);
    }
}
