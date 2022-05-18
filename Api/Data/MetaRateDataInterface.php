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
    const META_RATE_DATA = 'meta_rate_data';

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
     * @return array|null
     */
    public function getProductData(): ?array;

    /**
     * @param int $origin
     * @param array $productData
     * @return void
     */
    public function setProductData(int $origin, array $productData): void;

    /**
     * @return mixed
     */
    public function getOriginData(): ?array;

    /**
     * @param int $origin
     * @param array $originData
     * @return void
     */
    public function setOriginData(int $origin, array $originData): void;
}
