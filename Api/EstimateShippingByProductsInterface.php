<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api;

/**
 * @api
 */
interface EstimateShippingByProductsInterface
{
    /**
     * @param int[] $productIds
     * @param int $customerId
     * @return \Calcurates\ModuleMagento\Api\Data\SimpleRateInterface[]
     */
    public function estimate(array $productIds, int $customerId): array;
}
