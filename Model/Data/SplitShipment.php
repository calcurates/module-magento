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

    public function getOrigin()
    {
        return $this->_get(self::ORIGIN);
    }

    public function setOrigin($origin)
    {
        $this->setData(self::ORIGIN, $origin);
    }

    public function getMethod()
    {
        return $this->_get(self::METHOD);
    }

    public function setMethod($method)
    {
        $this->setData(self::METHOD, $method);
    }

    public function getPrice()
    {
        return $this->_get(self::PRICE);
    }

    public function setPrice($price)
    {
        $this->setData(self::PRICE, $price);
    }

    public function getCost()
    {
        return $this->_get(self::COST);
    }

    public function setCost($cost)
    {
        $this->setData(self::COST, $cost);
    }

    public function getProducts()
    {
        return $this->_get(self::PRODUCTS);
    }

    public function setProducts($products)
    {
        $this->setData(self::PRODUCTS, $products);
    }
}
