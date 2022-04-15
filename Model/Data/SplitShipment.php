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
     * @return mixed|null
     */
    public function getOrigin()
    {
        return $this->_get(self::ORIGIN);
    }

    /**
     * @param $origin
     * @return void
     */
    public function setOrigin($origin)
    {
        $this->setData(self::ORIGIN, $origin);
    }

    /**
     * @return mixed|null
     */
    public function getMethod()
    {
        return $this->_get(self::METHOD);
    }

    /**
     * @param $method
     * @return void
     */
    public function setMethod($method)
    {
        $this->setData(self::METHOD, $method);
    }

    /**
     * @return mixed|null
     */
    public function getPrice()
    {
        return $this->_get(self::PRICE);
    }

    /**
     * @param $price
     * @return void
     */
    public function setPrice($price)
    {
        $this->setData(self::PRICE, $price);
    }

    /**
     * @return mixed|null
     */
    public function getCost()
    {
        return $this->_get(self::COST);
    }

    /**
     * @param $cost
     * @return void
     */
    public function setCost($cost)
    {
        $this->setData(self::COST, $cost);
    }

    /**
     * @return mixed|null
     */
    public function getProducts()
    {
        return $this->_get(self::PRODUCTS);
    }

    /**
     * @param $products
     * @return void
     */
    public function setProducts($products)
    {
        $this->setData(self::PRODUCTS, $products);
    }
}
