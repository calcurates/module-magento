<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
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
    public function getFrontendLabel()
    {
        return $this->_getData(self::FRONTEND_LABEL);
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
    public function setFrontendLabel($frontendLabel)
    {
        return $this->setData(self::FRONTEND_LABEL, $frontendLabel);
    }
}
