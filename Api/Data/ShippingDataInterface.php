<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\Data;

interface ShippingDataInterface
{
    public const SOURCE_CODE = 'source_code';
    public const SHIPPING_SERVICE_ID = 'shipping_service_id';

    /**
     * @return string|null
     */
    public function getSourceCode();

    /**
     * @param string $sourceCode
     * @return ShippingDataInterface
     */
    public function setSourceCode($sourceCode);

    /**
     * @return string|null
     */
    public function getShippingServiceId();

    /**
     * @param string $shippingServiceId
     * @return ShippingDataInterface
     */
    public function setShippingServiceId($shippingServiceId);
}
