<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Model\AdminOrder;

use Magento\Checkout\Model\Cart\CartInterface;
use Calcurates\ModuleMagento\Client\Command\SendOrderInformationCommand;
use Magento\Sales\Model\Order;

class SendOrderInfo
{
    /**
     * @var SendOrderInformationCommand
     */
    private $sendOrderInformationCommand;

    /**
     * SendOrderInfo constructor.
     * @param SendOrderInformationCommand $sendOrderInformationCommand
     */
    public function __construct(
        SendOrderInformationCommand $sendOrderInformationCommand
    ) {
        $this->sendOrderInformationCommand = $sendOrderInformationCommand;
    }

    /**
     * @param CartInterface $subject
     * @param $order
     * @return mixed
     */
    public function afterCreateOrder(CartInterface $subject, $order)
    {
        if ($order instanceof Order && $order->getEntityId()) {
            $this->sendOrderInformationCommand->execute($order);
        }
        return $order;
    }
}
