<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Catalog\Product;

use Calcurates\ModuleMagento\Api\Catalog\Product\ProductAttributeListInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributesCustomDataInterfaceFactory;
use Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributeCustomDataOptionInterfaceFactory;
use Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributesCustomDataInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributeCustomDataOptionInterface;

class AttributeList implements ProductAttributeListInterface
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $eavAttributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var AttributesCustomDataInterfaceFactory
     */
    private $customDataFactory;

    /**
     * @var AttributeCustomDataOptionInterfaceFactory
     */
    private $customDataOptionFactory;

    /**
     * AttributeList constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $eavAttributeRepository
     * @param AttributesCustomDataInterfaceFactory $customDataFactory
     * @param AttributeCustomDataOptionInterfaceFactory $customDataOptionFactory
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $eavAttributeRepository,
        AttributesCustomDataInterfaceFactory $customDataFactory,
        AttributeCustomDataOptionInterfaceFactory $customDataOptionFactory
    ) {
        $this->eavAttributeRepository = $eavAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customDataFactory = $customDataFactory;
        $this->customDataOptionFactory = $customDataOptionFactory;
    }

    /**
     * @return AttributesCustomDataInterface[]
     */
    public function getItems(): array
    {
        /** @var SearchCriteria $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ProductAttributeInterface::ATTRIBUTE_ID, 1, 'gteq')
            ->create();

        $attributesItems = $this->eavAttributeRepository->getList(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteria
        )->getItems();

        $result = [];
        /** @var Attribute $attributesItem */
        foreach ($attributesItems as $attributesItem) {
            if (
                in_array($attributesItem->getFrontendInput(), ProductAttributeListInterface::BANNED_INPUT_TYPES, true)
            ) {
                continue;
            }
            if (
                in_array($attributesItem->getAttributeCode(), ProductAttributeListInterface::BANNED_ATTRIBUTES, true)
            ) {
                continue;
            }
            $values = [];
            try {
                $sourceOptions = $attributesItem->getSource()->getAllOptions();
            } catch (\Exception $e) {
                continue;
            }
            foreach ($sourceOptions as $option) {
                if (empty($option['value']) || is_array($option['value'])) {
                    continue;
                }
                $values[] = $this->getCustomDataOptionObject()
                    ->setLabel($option['label'])
                    ->setValue($option['value']);
            }
            if ($type = $this->resolveType($attributesItem, $values)) {
                $result[] = $this->getCustomDataObject()
                    ->setAttributeCode($attributesItem->getAttributeCode())
                    ->setFrontendLabel((string) $attributesItem->getDefaultFrontendLabel())
                    ->setValues($values)
                    ->setType($type);
            }
        }

        return $result;
    }

    /**
     * @return AttributesCustomDataInterface
     */
    private function getCustomDataObject(): AttributesCustomDataInterface
    {
        return $this->customDataFactory->create();
    }

    /**
     * @return AttributeCustomDataOptionInterface
     */
    private function getCustomDataOptionObject(): AttributeCustomDataOptionInterface
    {
        return $this->customDataOptionFactory->create();
    }

    /**
     * @param Attribute $attribute
     * @param array $values
     * @return string|null
     */
    private function resolveType(Attribute $attribute, array $values): ?string
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
