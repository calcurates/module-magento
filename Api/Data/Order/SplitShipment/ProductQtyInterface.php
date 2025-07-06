<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data\Order\SplitShipment;

interface ProductQtyInterface
{
    public const QTY = 'qty';
    public const SKU = 'sku';

    /**
     * @return string
     */
    public function getSku(): string;

    /**
     * @param string $sku
     * @return void
     */
    public function setSku(string $sku): void;

    /**
     * @return int
     */
    public function getQty(): int;

    /**
     * @param int $qty
     * @return void
     */
    public function setQty(int $qty): void;
}
