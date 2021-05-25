<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Data;

use Calcurates\ModuleMagento\Api\Data\ShippingDataInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

class ShippingData extends AbstractExtensibleObject implements ShippingDataInterface
{
    /**
     * @return string|null
     */
    public function getSourceCode()
    {
        return $this->_get(self::SOURCE_CODE);
    }

    /**
     * @return string|null
     */
    public function getShippingServiceId()
    {
        return $this->_get(self::SHIPPING_SERVICE_ID);
    }

    /**
     * @param string $sourceCode
     * @return $this
     */
    public function setSourceCode($sourceCode)
    {
        return $this->setData(self::SOURCE_CODE, $sourceCode);
    }

    /**
     * @param string $shippingServiceId
     * @return $this
     */
    public function setShippingServiceId($shippingServiceId)
    {
        return $this->setData(self::SHIPPING_SERVICE_ID, $shippingServiceId);
    }
}
