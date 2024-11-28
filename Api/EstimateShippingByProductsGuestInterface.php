<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api;

/**
 * @api
 */
interface EstimateShippingByProductsGuestInterface
{
    /**
     * @param int[] $productIds
     * @param string[]|null $shipTo
     * @return \Calcurates\ModuleMagento\Api\Data\SimpleRateInterface[]
     */
    public function estimate(array $productIds, ?array $shipTo = null): array;
}
