<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Data;

use Calcurates\ModuleMagento\Api\Data\SplitShipmentInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class SplitShipment extends AbstractSimpleObject implements SplitShipmentInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrigin(): ?string
    {
        return $this->_get(self::ORIGIN);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrigin(string $origin): void
    {
        $this->setData(self::ORIGIN, $origin);
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(): ?string
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
    public function getPrice(): ?float
    {
        return $this->_get(self::PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPrice($price): void
    {
        $this->setData(self::PRICE, $price);
    }

    /**
     * {@inheritdoc}
     */
    public function getCost(): ?float
    {
        return $this->_get(self::COST);
    }

    /**
     * {@inheritdoc}
     */
    public function setCost($cost): void
    {
        $this->setData(self::COST, $cost);
    }

    /**
     * {@inheritdoc}
     */
    public function getProducts(): ?array
    {
        return $this->_get(self::PRODUCTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setProducts($products): void
    {
        $this->setData(self::PRODUCTS, $products);
    }
}
