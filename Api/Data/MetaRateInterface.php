<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data;

interface MetaRateInterface
{
    const RATES = 'rates';
    const PRODUCTS = 'products';
    const ORIGIN = 'origin';

    /**
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    public function getRates();

    /**
     * @param array $rates
     * @return mixed
     */
    public function setRates(array $rates);

    /**
     * @return array
     */
    public function getProducts();

    /**
     * @param array $products
     * @return void
     */
    public function setProducts(array $products);

    /**
     * @return int
     */
    public function getOriginId();

    /**
     * @param int $id
     * @return void
     */
    public function setOriginId(int $id);
}
