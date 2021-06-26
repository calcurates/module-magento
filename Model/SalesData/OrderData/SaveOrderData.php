<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\SalesData\OrderData;

use Calcurates\ModuleMagento\Api\Data\OrderDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\OrderData\SaveOrderDataInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\OrderData;

class SaveOrderData implements SaveOrderDataInterface
{
    /**
     * @var OrderData
     */
    private $resource;

    public function __construct(OrderData $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param OrderDataInterface $orderData
     * @return OrderDataInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(
        OrderDataInterface $orderData
    ): OrderDataInterface {
        $this->resource->save($orderData);

        return $orderData;
    }
}
