<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

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
