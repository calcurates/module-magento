<?php

namespace Calcurates\ModuleMagento\Api\Data\Checkout\AttributeLinking;

interface CustomAttributeInterface
{
    const ATTRIBUTE_CODE = 'attribute_code';

    /**
     * @param string $attributeCode
     *
     * @return CustomAttributeInterface
     */
    public function setAttributeCode($attributeCode);

    /**
     * @return string
     */
    public function getAttributeCode();
}
