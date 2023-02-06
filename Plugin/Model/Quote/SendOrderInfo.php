<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Model\Quote;

use Calcurates\ModuleMagento\Client\Request\OrderInfoRequestBuilder;
use Calcurates\ModuleMagento\Api\Client\CalcuratesClientInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class SendOrderInfo
 * @package Calcurates\ModuleMagento\Plugin\Model\Quote
 */
class SendOrderInfo
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
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * SendOrderInfo constructor.
     * @param CalcuratesClientInterface $calcuratesClient
     * @param OrderInfoRequestBuilder $orderInfoRequestBuilder
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        CalcuratesClientInterface $calcuratesClient,
        OrderInfoRequestBuilder $orderInfoRequestBuilder,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->calcuratesClient = $calcuratesClient;
        $this->orderInfoRequestBuilder = $orderInfoRequestBuilder;
    }

    /**
     * @param CartManagementInterface $subject
     * @param $result
     * @return mixed
     */
    public function afterPlaceOrder(CartManagementInterface $subject, $result)
    {
        if ($result) {
            try {
                $order = $this->orderRepository->get($result);
            } catch (\Exception $e) {
                return $result;
            }
            $orderData = $this->orderInfoRequestBuilder->build($order);
            if (is_array($orderData) && $orderData) {
                $this->calcuratesClient->populateOrderInfo($orderData, $order->getStoreId());
            }
        }
        return $result;
    }
}
