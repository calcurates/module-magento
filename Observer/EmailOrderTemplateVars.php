<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Observer;

use Calcurates\ModuleMagento\ViewModel\OrderDeliveryDate;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class EmailOrderTemplateVars implements ObserverInterface
{
    /**
     * @var OrderDeliveryDate
     */
    private $orderDeliveryDate;

    /**
     * @param OrderDeliveryDate $orderDeliveryDate
     */
    public function __construct(OrderDeliveryDate $orderDeliveryDate)
    {
        $this->orderDeliveryDate = $orderDeliveryDate;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $transportObject = $observer->getData('transportObject');
        $orderId = $transportObject->getOrderId();
        $orderData = $this->orderDeliveryDate->getOrderDataByOrderId($orderId);
        if ($orderData && $orderData->getDeliveryDate()) {
            $transportObject->setSelectedDeliveryDate(
                $this->orderDeliveryDate->formatDate($orderData->getDeliveryDate())
            );
            if ($orderData->getDeliveryDateTimeFrom() || $orderData->getDeliveryDateTimeTo()) {
                $transportObject->setSelectedDeliveryTime(
                    $this->orderDeliveryDate->formatTimeInterval(
                        $orderData->getDeliveryDateTimeFrom(),
                        $orderData->getDeliveryDateTimeTo()
                    )
                );
            }
        }
    }
}
