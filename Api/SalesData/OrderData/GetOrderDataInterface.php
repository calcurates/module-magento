<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\SalesData\OrderData;

interface GetOrderDataInterface
{
    /**
     * @param int $orderId
     * @return \Calcurates\ModuleMagento\Api\Data\OrderDataInterface|null
     */
    public function get(int $orderId): ?\Calcurates\ModuleMagento\Api\Data\OrderDataInterface;
}
