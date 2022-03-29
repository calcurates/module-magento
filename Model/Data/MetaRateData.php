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
     * @var
     */
    protected $rates;

    /**
     * @var
     */
    protected $products;

    /**
     * @var
     */
    protected $origins;

    /**
     * @param $code
     * @return mixed|null
     */
    public function getRatesData($code = null)
    {
        if (null === $code) {
            return $this->rates;
        }
        return $this->rates[$code] ?? null;
    }

    /**
     * @param $originId
     * @param $rateData
     * @return mixed|void
     */
    public function setRatesData($originId, $rateData)
    {
        $this->rates[$originId] = $rateData->getAllRates();
    }

    /**
     * @param $code
     * @return mixed|null
     */
    public function getProductData($code = null)
    {
        if (null === $code) {
            return $this->products;
        }
        return $this->products[$code] ?? null;
    }

    /**
     * @param $origin
     * @param $productData
     * @return mixed|void
     */
    public function setProductData($origin, $productData)
    {
        $this->products[$origin] = $productData;
    }

    public function getOriginData($code = null)
    {
        if (null === $code) {
            return $this->origins;
        }
        return $this->origins[$code] ?? null;
    }

    public function setOriginData($origin, $originData)
    {
        $this->origins[$origin] = $originData;
    }
}
