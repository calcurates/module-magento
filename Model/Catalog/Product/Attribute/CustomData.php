<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Catalog\Product\Attribute;

use Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributeCustomDataInterface;

class CustomData extends \Magento\Framework\DataObject implements AttributeCustomDataInterface
{
    /**
     * @inheritDoc
     */
    public function getAttributeId()
    {
        return (int)$this->_getData(self::ATTRIBUTE_ID);
    }

    /**
     * @inheritDoc
     */
    public function getAttributeCode()
    {
        return $this->_getData(self::ATTRIBUTE_CODE);
    }

    /**
     * @inheritDoc
     */
    public function getAttributeBackendType()
    {
        return $this->_getData(self::ATTRIBUTE_BACKEND_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function getAttributeFrontendType()
    {
        return $this->_getData(self::ATTRIBUTE_FRONTEND_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function getFrontendLabel()
    {
        return $this->_getData(self::FRONTEND_LABEL);
    }

    /**
     * @inheritDoc
     */
    public function getValues()
    {
        return $this->_getData(self::VALUES);
    }

    /**
     * @inheritDoc
     */
    public function setAttributeId($attributeId)
    {
        return $this->setData(self::ATTRIBUTE_ID, $attributeId);
    }

    /**
     * @inheritDoc
     */
    public function setAttributeCode($attributeCode)
    {
        return $this->setData(self::ATTRIBUTE_CODE, $attributeCode);
    }

    /**
     * @inheritDoc
     */
    public function setAttributeBackendType($attributeBackendType)
    {
        return $this->setData(self::ATTRIBUTE_BACKEND_TYPE, $attributeBackendType);
    }

    /**
     * @inheritDoc
     */
    public function setAttributeFrontendType($attributeFrontendType)
    {
        return $this->setData(self::ATTRIBUTE_FRONTEND_TYPE, $attributeFrontendType);
    }

    /**
     * @inheritDoc
     */
    public function setFrontendLabel($frontendLabel)
    {
        return $this->setData(self::FRONTEND_LABEL, $frontendLabel);
    }

    /**
     * @inheritDoc
     */
    public function setValues($values)
    {
        return $this->setData(self::VALUES, $values);
    }
}
