<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Catalog\Product\Attribute;

use Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributesCustomDataInterface;
use Magento\Framework\DataObject;

class Data extends DataObject implements AttributesCustomDataInterface
{
    /**
     * @inheritDoc
     */
    public function getAttributeCode(): string
    {
        return $this->_getData(self::ATTRIBUTE_CODE);
    }

    /**
     * @inheritDoc
     */
    public function getFrontendLabel(): ?string
    {
        return $this->_getData(self::FRONTEND_LABEL);
    }

    /**
     * @inheritDoc
     */
    public function getValues(): ?array
    {
        return $this->_getData(self::VALUES);
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->_getData(self::TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setFrontendLabel(?string $frontendLabel): AttributesCustomDataInterface
    {
        return $this->setData(self::FRONTEND_LABEL, $frontendLabel);
    }

    /**
     * @inheritDoc
     */
    public function setAttributeCode(string $attributeCode): AttributesCustomDataInterface
    {
        return $this->setData(self::ATTRIBUTE_CODE, $attributeCode);
    }

    /**
     * @inheritDoc
     */
    public function setValues(?array $values): AttributesCustomDataInterface
    {
        return $this->setData(self::VALUES, $values);
    }

    /**
     * @inheritDoc
     */
    public function setType(string $type): AttributesCustomDataInterface
    {
        return $this->setData(self::TYPE, $type);
    }
}
