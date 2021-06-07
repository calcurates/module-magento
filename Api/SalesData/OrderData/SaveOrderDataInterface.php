<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\SalesData\OrderData;

interface SaveOrderDataInterface
{
    /**
     * @param \Calcurates\ModuleMagento\Api\Data\OrderDataInterface $orderData
     * @return \Calcurates\ModuleMagento\Api\Data\OrderDataInterface
     */
    public function save(
        \Calcurates\ModuleMagento\Api\Data\OrderDataInterface $orderData
    ): \Calcurates\ModuleMagento\Api\Data\OrderDataInterface;
}
