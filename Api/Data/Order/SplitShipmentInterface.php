<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data\Order;

interface SplitShipmentInterface extends \Calcurates\ModuleMagento\Api\Data\SplitShipmentInterface
{
    public const PRODUCT_QTY = 'product_qty';
    public const TITLE = 'title';
    public const CODE = 'code';

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @param string $title
     * @return void
     */
    public function setTitle(string $title): void;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param string $code
     * @return void
     */
    public function setCode(string $code): void;

    /**
     * @return \Calcurates\ModuleMagento\Api\Data\Order\SplitShipment\ProductQtyInterface[]
     */
    public function getProductQty(): array;

    /**
     * @param \Calcurates\ModuleMagento\Api\Data\Order\SplitShipment\ProductQtyInterface[] $productQty
     * @return void
     */
    public function setProductQty(array $productQty): void;

}
