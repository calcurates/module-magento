<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data;

interface MetaRateDataInterface
{
    public const META_RATE_DATA = 'meta_rate_data';

    /**
     * @return array|null
     */
    public function getRatesData(): ?array;

    /**
     * @param int $originId
     * @param array $rateData
     * @return void
     */
    public function setRatesData(int $originId, array $rateData): void;

    /**
     * @param string|null $code
     * @return array|null
     */
    public function getProductData($code = null): ?array;

    /**
     * @param int $origin
     * @param array $productData
     * @return void
     */
    public function setProductData(int $origin, array $productData): void;

    /**
     * @param string|null $code
     * @return array|null
     */
    public function getOriginData($code = null): ?array;

    /**
     * @param int $origin
     * @param array $originData
     * @return void
     */
    public function setOriginData(int $origin, array $originData): void;

    /**
     * @param string|null $code
     * @return array|null
     */
    public function getProductQtys($code = null): ?array;

    /**
     * @param int $origin
     * @param array $productData
     * @return void
     */
    public function setProductQtys(int $origin, array $productData): void;
}
