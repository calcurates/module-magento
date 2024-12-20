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
use Calcurates\ModuleMagento\Api\Data\OrderDataInterfaceFactory;
use Calcurates\ModuleMagento\Api\SalesData\OrderData\GetOrderDataInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\OrderData;

class GetOrderData implements GetOrderDataInterface
{
    /**
     * @var OrderData
     */
    private $resource;

    /**
     * @var OrderDataInterfaceFactory
     */
    private $factory;

    public function __construct(OrderData $resource, OrderDataInterfaceFactory $factory)
    {
        $this->resource = $resource;
        $this->factory = $factory;
    }

    /**
     * @param int $orderId
     * @return OrderDataInterface|null
     */
    public function get(int $orderId): ?OrderDataInterface
    {
        $model = $this->factory->create();
        $this->resource->load($model, $orderId, OrderDataInterface::ORDER_ID);

        return $model->getId() ? $model : null;
    }
}
