<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api;

/**
 * @api
 */
interface EstimateShippingByProductsGuestInterface
{
    /**
     * @param int[] $productIds
     * @return \Calcurates\ModuleMagento\Api\Data\SimpleRateInterface[]
     */
    public function estimate(array $productIds): array;
}
