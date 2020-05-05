<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Source\Algorithms;

use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderItemsRetriever
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * ItemsDataRetriever constructor.
     * @param RequestInterface $request
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        RequestInterface $request,
        OrderItemRepositoryInterface $orderItemRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->request = $request;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return array
     */
    public function getOrderItems()
    {
        $requestData = $this->request->getParam('requestData');
        $orderId = (int) $this->request->getParam('order_id');
        $orderItems = [];
        if ($requestData) {
            foreach ($requestData as $item) {
                $orderItems[] = $this->orderItemRepository->get($item['orderItem']);
            }
        } elseif ($orderId) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($orderId);
            foreach ($order->getAllItems() as $orderItem) {
                if ($orderItem->getIsVirtual()
                    || $orderItem->getLockedDoShip()
                    || $orderItem->getHasChildren()) {
                    continue;
                }
                $orderItems[] = $orderItem;
            }
        }

        return $orderItems;
    }
}
