<?php

namespace Calcurates\ModuleMagento\Api\Data\Catalog\Product;

interface AttributeCustomDataOptionInterface
{
    const VALUE = 'value';
    const LABEL = 'label';

    /**
     * @return string
     */
    public function getValue();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param string $value
     *
     * @return AttributeCustomDataOptionInterface
     */
    public function setValue($value);

    /**
     * @param string $label
     *
     * @return AttributeCustomDataOptionInterface
     */
    public function setLabel($label);
}
