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
     * @return string|null
     */
    public function getOrigin(): ?string;

    /**
     * @param string $origin
     * @return void
     */
    public function setOrigin(string $origin): void;

    /**
     * @return string|null
     */
    public function getMethod(): ?string;

    /**
     * @param string $method
     * @return void
     */
    public function setMethod(string $method): void;

    /**
     * @return float|null
     */
    public function getPrice(): ?float;

    /**
     * @param float|null $price
     * @return void
     */
    public function setPrice($price): void;

    /**
     * @return float|null
     */
    public function getCost(): ?float;

    /**
     * @param float|null $cost
     * @return void
     */
    public function setCost($cost): void;

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface[]|null
     */
    public function getProducts(): ?array;

    /**
     * @param array|null $products
     * @return void
     */
    public function setProducts($products): void;
}
