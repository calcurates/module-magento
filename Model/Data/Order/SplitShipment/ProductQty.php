<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */
declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Data\Order\SplitShipment;

use Calcurates\ModuleMagento\Api\Data\Order\SplitShipment\ProductQtyInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class ProductQty extends AbstractSimpleObject implements ProductQtyInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSku(): string
    {
        return $this->_get(self::SKU);
    }

    /**
     * {@inheritdoc}
     */
    public function setSku(string $sku): void
    {
        $this->setData(self::SKU, $sku);
    }

    /**
     * {@inheritdoc}
     */
    public function getQty(): float
    {
        return $this->_get(self::QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function setQty(float $qty): void
    {
        $this->setData(self::QTY, $qty);
    }
}
