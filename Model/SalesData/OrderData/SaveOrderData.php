<?php

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
