<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\CheckoutConverter;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
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
        $orderChanged = false;
        $deliveryDates = $quote->getData(CustomSalesAttributesInterface::DELIVERY_DATES);
        if ($deliveryDates) {
            $order->setData(CustomSalesAttributesInterface::DELIVERY_DATES, $deliveryDates);
            $orderChanged = true;
        }

        $carrierData = $this->shippingMethodManager->getCarrierData($order->getShippingMethod(false));

        if ($carrierData) {
            $this->convertPackages->convert($quote, $order, $carrierData);
            $this->convertServicesSources->convert($quote, $order);
            $orderChanged = true;
        }

        if ($orderChanged) {
            $this->orderRepository->save($order);
        }
    }
}
