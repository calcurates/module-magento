<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data;

interface SplitShipmentInterface
{
    public const ORIGIN = 'origin';
    public const METHOD = 'method';
    public const COST = 'cost';
    public const PRICE = 'price';
    public const PRODUCTS = 'products';

    /**
     * @return mixed
     */
    public function getOrigin();

    /**
     * @param $origin
     * @return mixed
     */
    public function setOrigin($origin);

    /**
     * @return mixed
     */
    public function getMethod();

    /**
     * @param $method
     * @return mixed
     */
    public function setMethod($method);

    /**
     * @return mixed
     */
    public function getPrice();

    /**
     * @param $price
     * @return mixed
     */
    public function setPrice($price);

    /**
     * @return mixed
     */
    public function getCost();

    /**
     * @param $cost
     * @return mixed
     */
    public function setCost($cost);

    /**
     * @return mixed
     */
    public function getProducts();

    /**
     * @param $products
     * @return mixed
     */
    public function setProducts($products);
}
