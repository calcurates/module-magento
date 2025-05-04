<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\Data\Catalog\Product;

/**
 * @api
 */
interface AttributesCustomDataInterface
{
    public const ATTRIBUTE_CODE = 'attribute_code';
    public const FRONTEND_LABEL = 'frontend_label';
    public const VALUES = 'values';
    public const TYPE = 'type';

    public const ATTRIBUTE_TYPE_NUMBER = 'number';
    public const ATTRIBUTE_TYPE_STRING = 'string';
    public const ATTRIBUTE_TYPE_BOOL = 'bool';
    public const ATTRIBUTE_TYPE_COLLECTION = 'collection';

    /**
     * @return string
     */
    public function getAttributeCode(): string;

    /**
     * @return string|null
     */
    public function getFrontendLabel(): ?string;

    /**
     * @return \Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributeCustomDataOptionInterface[]|null $values
     */
    public function getValues(): ?array;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string||null $frontendLabel
     *
     * @return $this
     */
    public function setFrontendLabel(?string $frontendLabel): AttributesCustomDataInterface;

    /**
     * @param string $attributeCode
     *
     * @return $this
     */
    public function setAttributeCode(string $attributeCode): AttributesCustomDataInterface;

    /**
     * @param \Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributeCustomDataOptionInterface[]|null $values
     *
     * @return $this
     */
    public function setValues(?array $values): AttributesCustomDataInterface;

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type): AttributesCustomDataInterface;
}
