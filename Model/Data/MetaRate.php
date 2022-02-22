<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Data;

use Calcurates\ModuleMagento\Api\Data\MetaRateInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class MetaRate extends AbstractSimpleObject implements MetaRateInterface
{

    /**
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    public function getRates()
    {
        return $this->_get(self::RATES);
    }

    /**
     * @param array $rates
     * @return void
     */
    public function setRates(array $rates)
    {
        $this->setData(self::RATES, $rates);
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->_get(self::PRODUCTS);
    }

    /**
     * @param array $products
     * @return void
     */
    public function setProducts(array $products)
    {
        $this->setData(self::PRODUCTS, $products);
    }

    /**
     * @return int|mixed|null
     */
    public function getOriginId()
    {
        return $this->_get(self::ORIGIN);
    }

    /**
     * @param int $id
     * @return void
     */
    public function setOriginId(int $id)
    {
        $this->setData(self::ORIGIN, $id);
    }
}
