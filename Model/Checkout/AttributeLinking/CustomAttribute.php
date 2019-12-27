<?php

namespace Calcurates\ModuleMagento\Model\Checkout\AttributeLinking;

use Calcurates\ModuleMagento\Api\Data\Checkout\AttributeLinking\CustomAttributeInterface;

class CustomAttribute extends \Magento\Framework\DataObject implements CustomAttributeInterface
{

    /**
     * {@inheritDoc}
     */
    public function setAttributeCode($attributeCode)
    {
        return $this->setData(static::ATTRIBUTE_CODE, $attributeCode);
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeCode()
    {
        return (string) $this->getData(static::ATTRIBUTE_CODE);
    }
}
