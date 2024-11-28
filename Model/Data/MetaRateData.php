<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Data;

use Calcurates\ModuleMagento\Api\Data\MetaRateDataInterface;
use Magento\Framework\DataObject;

class MetaRateData extends DataObject implements MetaRateDataInterface
{
    /**
     * @var array
     */
    protected $rates = [];

    /**
     * @var array
     */
    protected $products = [];

    /**
     * @var array
     */
    protected $origins = [];

    /**
     * @var array
     */
    protected $productQtys;

    /**
     * @param string|null $code
     * @return array|null
     */
    public function getRatesData(?string $code = null): ?array
    {
        if (null === $code) {
            return $this->rates;
        }
        return $this->rates[$code] ?? null;
    }

    /**
     * @param int $originId
     * @param array $rateData
     * @return void
     */
    public function setRatesData(int $originId, array $rateData): void
    {
        $this->rates[$originId] = $rateData;
    }

    /**
     * @param string|null $code
     * @return array|null
     */
    public function getProductData($code = null): ?array
    {
        if (null === $code) {
            return $this->products;
        }
        return $this->products[$code] ?? null;
    }

    /**
     * @param int $origin
     * @param array $productData
     * @return void
     */
    public function setProductData(int $origin, array $productData): void
    {
        $this->products[$origin] = $productData;
    }

    /**
     * @param string|null $code
     * @return array|null
     */
    public function getOriginData($code = null): ?array
    {
        if (null === $code) {
            return $this->origins;
        }
        return $this->origins[$code] ?? null;
    }

    /**
     * @param int $origin
     * @param array $originData
     * @return void
     */
    public function setOriginData(int $origin, array $originData): void
    {
        $this->origins[$origin] = $originData;
    }

    /**
     * @param string|null $code
     * @return array|null
     */
    public function getProductQtys($code = null): ?array
    {
        if (null === $code) {
            return $this->productQtys;
        }
        return $this->productQtys[$code] ?? null;
    }

    /**
     * @param int $origin
     * @param array $productData
     * @return void
     */
    public function setProductQtys(int $origin, array $productData): void
    {
        $this->productQtys[$origin] = $productData;
    }
}
