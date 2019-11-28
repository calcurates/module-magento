<?php

namespace Calcurates\ModuleMagento\Api\System\Checkout\Attributes;

interface CustomAttributesInterface
{
    /**
     * @param \Calcurates\ModuleMagento\Api\Data\Checkout\AttributeLinking\CustomAttributeInterface[] $attributes
     *
     * @param int $websiteId
     *
     * @return void
     */
    public function save($attributes, $websiteId);
}
