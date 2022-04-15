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
     * @return mixed
     */
    public function getRatesData();

    /**
     * @param $originId
     * @param $rateData
     * @return mixed
     */
    public function setRatesData($originId, $rateData);

    /**
     * @return mixed
     */
    public function getProductData();

    /**
     * @param $origin
     * @param $productData
     * @return mixed
     */
    public function setProductData($origin, $productData);

    /**
     * @return mixed
     */
    public function getOriginData();

    /**
     * @param $origin
     * @param $originData
     * @return mixed
     */
    public function setOriginData($origin, $originData);
}
