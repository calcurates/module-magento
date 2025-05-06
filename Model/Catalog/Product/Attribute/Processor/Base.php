<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Catalog\Product\Attribute\Processor;

use Calcurates\ModuleMagento\Api\Data\Catalog\Product\Attribute\ProcessorInterface;
use Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributesCustomDataInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributesCustomDataInterfaceFactory;
use Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributeCustomDataOptionInterfaceFactory;
use Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributeCustomDataOptionInterface;

class Base implements ProcessorInterface
{
    /**
     * @var AttributesCustomDataInterfaceFactory
     */
    protected $customDataFactory;

    /**
     * @var AttributeCustomDataOptionInterfaceFactory
     */
    protected $customDataOptionFactory;

    /**
     * Base constructor.
     * @param AttributesCustomDataInterfaceFactory $customDataFactory
     * @param AttributeCustomDataOptionInterfaceFactory $customDataOptionFactory
     */
    public function __construct(
        AttributesCustomDataInterfaceFactory $customDataFactory,
        AttributeCustomDataOptionInterfaceFactory $customDataOptionFactory
    ) {
        $this->customDataFactory = $customDataFactory;
        $this->customDataOptionFactory = $customDataOptionFactory;
    }

    /**
     * @param ProductAttributeInterface $attribute
     * @param int|null $websiteId
     * @return AttributesCustomDataInterface|null
     */
    public function process(ProductAttributeInterface $attribute, ?int $websiteId): ?AttributesCustomDataInterface
    {
        $values = [];
        $result = null;
        try {
            $sourceOptions = $attribute->getSource()->getAllOptions();
        } catch (\Exception $e) {
            $sourceOptions = [];
        }
        foreach ($sourceOptions as $option) {
            if (empty($option['value']) || is_array($option['value'])) {
                continue;
            }
            $values[] = $this->getCustomDataOptionObject()
                ->setLabel($option['label'])
                ->setValue($option['value']);
        }
        if ($type = $this->resolveType($attribute, $values)) {
            $result = $this->getCustomDataObject()
                ->setAttributeCode($attribute->getAttributeCode())
                ->setFrontendLabel((string) $attribute->getDefaultFrontendLabel())
                ->setValues($values)
                ->setType($type)
                ->setCanMulti($attribute->getFrontendInput() === 'multiselect')
            ;
        }
        return $result;
    }

    /**
     * @param string $attributeCode
     * @return bool
     */
    public function canProcess(string $attributeCode): bool
    {
        return true;
    }

    /**
     * @return AttributesCustomDataInterface
     */
    protected function getCustomDataObject(): AttributesCustomDataInterface
    {
        return $this->customDataFactory->create();
    }

    /**
     * @return AttributeCustomDataOptionInterface
     */
    protected function getCustomDataOptionObject(): AttributeCustomDataOptionInterface
    {
        return $this->customDataOptionFactory->create();
    }

    /**
     * @param ProductAttributeInterface $attribute
     * @param array $values
     * @return string|null
     */
    protected function resolveType(ProductAttributeInterface $attribute, array $values): ?string
    {
        $frontendType = $attribute->getFrontendInput();
        $backendType = $attribute->getBackendType();
        if ('boolean' === $frontendType
            || ('text' === $frontendType && 'int' === $backendType && $values && 1 === count($values))
        ) {
            return AttributesCustomDataInterface::ATTRIBUTE_TYPE_BOOL;
        }
        if (in_array($backendType, ['decimal',  'int'], true)
            || $attribute->getFrontendClass() == 'validate-number'
        ) {
            return AttributesCustomDataInterface::ATTRIBUTE_TYPE_NUMBER;
        }
        if (in_array($frontendType, ['select', 'multiselect'], true)) {
            return AttributesCustomDataInterface::ATTRIBUTE_TYPE_COLLECTION;
        }
        if (in_array($backendType, ['varchar', 'text', 'static'], true)
            && in_array($frontendType, ['text', 'textarea'], true)
        ) {
            return AttributesCustomDataInterface::ATTRIBUTE_TYPE_STRING;
        }
        return null;
    }
}
