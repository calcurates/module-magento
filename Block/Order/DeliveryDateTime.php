<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Block\Order;

use Calcurates\ModuleMagento\ViewModel\OrderDeliveryDate;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class DeliveryDateTime extends Template
{
    /**
     * @var OrderDeliveryDate
     */
    private $orderDeliveryDate;

    /**
     * @param Context $context
     * @param OrderDeliveryDate $orderDeliveryDate
     * @param array $data
     */
    public function __construct(
        Context $context,
        OrderDeliveryDate $orderDeliveryDate,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderDeliveryDate = $orderDeliveryDate;
    }

    /**
     * @return OrderDeliveryDate
     */
    public function getOrderDeliveryDate(): OrderDeliveryDate
    {
        return $this->orderDeliveryDate;
    }
}
