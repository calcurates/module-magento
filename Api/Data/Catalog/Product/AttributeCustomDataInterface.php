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
interface AttributeCustomDataInterface
{
    const ATTRIBUTE_ID = 'attribute_id';
    const ATTRIBUTE_CODE = 'attribute_code';
    const ATTRIBUTE_BACKEND_TYPE = 'attribute_backend_type';
    const ATTRIBUTE_FRONTEND_TYPE = 'attribute_frontend_type';
    const FRONTEND_LABEL = 'frontend_label';
    const VALUES = 'values';

    /**
     * @return int
     */
    public function getAttributeId();

    /**
     * @return string
     */
    public function getAttributeCode();

    /**
     * @return string
     */
    public function getAttributeBackendType();

    /**
     * @return string
     */
    public function getAttributeFrontendType();

    /**
     * @return string
     */
    public function getFrontendLabel();

    /**
     * @return \Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributeCustomDataOptionInterface[] $values
     */
    public function getValues();

    /**
     * @param int $attributeId
     *
     * @return AttributeCustomDataInterface
     */
    public function setAttributeId($attributeId);

    /**
     * @param string $frontendLabel
     *
     * @return AttributeCustomDataInterface
     */
    public function setFrontendLabel($frontendLabel);

    /**
     * @param string $attributeCode
     *
     * @return AttributeCustomDataInterface
     */
    public function setAttributeCode($attributeCode);

    /**
     * @param string $attributeBackendType
     *
     * @return AttributeCustomDataInterface
     */
    public function setAttributeBackendType($attributeBackendType);

    /**
     * @param string $attributeFrontendType
     *
     * @return AttributeCustomDataInterface
     */
    public function setAttributeFrontendType($attributeFrontendType);

    /**
     * @param \Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributeCustomDataOptionInterface[] $values
     *
     * @return AttributeCustomDataInterface
     */
    public function setValues($values);
}
