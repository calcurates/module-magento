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
    public const RATES = 'rates';
    public const PRODUCTS = 'products';
    public const ORIGIN = 'origin';

    /**
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    public function getRates(): array;

    /**
     * @param array $rates
     * @return void
     */
    public function setRates(array $rates): void;

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface[]|null
     */
    public function getProducts(): ?array;

    /**
     * @param array $products
     * @return void
     */
    public function setProducts(array $products): void;

    /**
     * @return int|null
     */
    public function getOriginId(): ?int;

    /**
     * @param int $id
     * @return void
     */
    public function setOriginId(int $id): void;
}
