<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */
declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Data\Order;

use Calcurates\ModuleMagento\Api\Data\Order\SplitShipmentInterface;

class SplitShipment extends \Calcurates\ModuleMagento\Model\Data\SplitShipment implements SplitShipmentInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTitle(): string
    {
        return $this->_get(self::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle(string $title): void
    {
        $this->setData(self::TITLE, $title);
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return $this->_get(self::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCode(string $code): void
    {
        $this->setData(self::CODE, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductQty(): ?array
    {
        return $this->_get(self::PRODUCT_QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductQty(?array $productQty): void
    {
        $this->setData(self::PRODUCT_QTY, $productQty);
    }
}
