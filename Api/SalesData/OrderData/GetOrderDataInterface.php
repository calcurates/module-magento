<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

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
