<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Order\Item;

use Magento\Framework\Api\AbstractSimpleObject;
use Calcurates\ModuleMagento\Api\Data\Order\Item\ShippingInformationInterface;

class ShippingInformation extends AbstractSimpleObject implements ShippingInformationInterface
{
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

    /**
     * {@inheritdoc}
     */
    public function getMethod(): string
    {
        return $this->_get(self::METHOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setMethod(string $method): void
    {
        $this->setData(self::METHOD, $method);
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodPrice(): float
    {
        return $this->_get(self::METHOD_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setMethodPrice(float $price): void
    {
        $this->setData(self::METHOD_PRICE, $price);
    }

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
}
