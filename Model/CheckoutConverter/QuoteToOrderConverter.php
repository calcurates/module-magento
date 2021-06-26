<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

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

    /**
     * @var ConvertQuoteData
     */
    private $convertQuoteData;

    public function __construct(
        ConvertPackages $convertPackages,
        ConvertServicesSources $convertServicesSources,
        ShippingMethodManager $shippingMethodManager,
        OrderRepositoryInterface $orderRepository,
        ConvertQuoteData $convertQuoteData
    ) {
        $this->convertPackages = $convertPackages;
        $this->convertServicesSources = $convertServicesSources;
        $this->shippingMethodManager = $shippingMethodManager;
        $this->orderRepository = $orderRepository;
        $this->convertQuoteData = $convertQuoteData;
    }

    /**
     * @param Quote $quote
     * @param Order $order
     */
    public function convert(Quote $quote, Order $order): void
    {
        // @TODO: store all data in external table
        $orderChanged = false;

        $carrierData = $this->shippingMethodManager->getCarrierData($order->getShippingMethod(false));

        if ($carrierData) {
            $this->convertPackages->convert($quote, $order, $carrierData);
            $this->convertServicesSources->convert($quote, $order);
            $orderChanged = true;
        }

        $this->convertQuoteData->convert($quote, $order);

        if ($orderChanged) {
            $this->orderRepository->save($order);
        }
    }
}
