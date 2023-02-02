<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2023 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */
namespace Calcurates\ModuleMagento\Observer;

use Calcurates\ModuleMagento\Client\Request\OrderInfoRequestBuilder;
use Calcurates\ModuleMagento\Api\Client\CalcuratesClientInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class PopulateOrderInformationAfterOrderPlaceObserver implements ObserverInterface
{
    /**
     * @var CalcuratesClientInterface
     */
    private $calcuratesClient;

    /**
     * @var OrderInfoRequestBuilder
     */
    private $orderInfoRequestBuilder;

    /**
     * PopulateOrderInformationAfterOrderPlaceObserver constructor.
     * @param CalcuratesClientInterface $calcuratesClient
     * @param OrderInfoRequestBuilder $orderInfoRequestBuilder
     */
    public function __construct(
        CalcuratesClientInterface $calcuratesClient,
        OrderInfoRequestBuilder $orderInfoRequestBuilder
    ) {
        $this->calcuratesClient = $calcuratesClient;
        $this->orderInfoRequestBuilder = $orderInfoRequestBuilder;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getData('order');
        if (!$order instanceof Order || !$order->getEntityId()) {
            return;
        }
        $orderData = $this->orderInfoRequestBuilder->build($order);
        if (is_array($orderData) && $orderData) {
            $this->calcuratesClient->populateOrderInfo($orderData, $order->getStoreId());
        }
    }
}
