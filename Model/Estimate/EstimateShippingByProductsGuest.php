<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Estimate;

use Calcurates\ModuleMagento\Api\EstimateShippingByProductsGuestInterface;
use Calcurates\ModuleMagento\Api\EstimateShippingByProductsInterface;

class EstimateShippingByProductsGuest implements EstimateShippingByProductsGuestInterface
{
    /**
     * @var EstimateShippingByProductsInterface
     */
    private $estimateShippingByProduct;

    public function __construct(EstimateShippingByProductsInterface $estimateShippingByProduct)
    {
        $this->estimateShippingByProduct = $estimateShippingByProduct;
    }

    /**
     * @param int[] $productIds
     * @return \Calcurates\ModuleMagento\Api\Data\SimpleRateInterface[]
     */
    public function estimate(array $productIds): array
    {
        return $this->estimateShippingByProduct->estimate($productIds, 0);
    }
}
