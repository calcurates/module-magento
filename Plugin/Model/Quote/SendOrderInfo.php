<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Model\Quote;

use Magento\Quote\Api\CartManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Calcurates\ModuleMagento\Client\Command\SendOrderInformationCommand;

class SendOrderInfo
{
    /**
     * @var SendOrderInformationCommand
     */
    private $sendOrderInformationCommand;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * SendOrderInfo constructor.
     * @param SendOrderInformationCommand $sendOrderInformationCommand
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        SendOrderInformationCommand $sendOrderInformationCommand,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->sendOrderInformationCommand = $sendOrderInformationCommand;
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
            $this->sendOrderInformationCommand->execute($order);
        }
        return $result;
    }
}
