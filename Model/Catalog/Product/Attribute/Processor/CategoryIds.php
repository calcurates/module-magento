<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Catalog\Product\Attribute\Processor;

use Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributesCustomDataInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Calcurates\ModuleMagento\Api\Catalog\CategoryManagementInterface;
use Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributesCustomDataInterfaceFactory;
use Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributeCustomDataOptionInterfaceFactory;
use Magento\Catalog\Api\Data\CategoryTreeInterface;

class CategoryIds extends Base
{
    /**
     * @var CategoryManagementInterface
     */
    private $categoryManagement;

    /**
     * CategoryIds constructor.
     * @param AttributesCustomDataInterfaceFactory $customDataFactory
     * @param AttributeCustomDataOptionInterfaceFactory $customDataOptionFactory
     * @param CategoryManagementInterface $categoryManagement
     */
    public function __construct(
        AttributesCustomDataInterfaceFactory $customDataFactory,
        AttributeCustomDataOptionInterfaceFactory $customDataOptionFactory,
        CategoryManagementInterface $categoryManagement
    ) {
        parent::__construct($customDataFactory, $customDataOptionFactory);
        $this->categoryManagement = $categoryManagement;
    }

    /**
     * @param ProductAttributeInterface $attribute
     * @param int $websiteId
     * @return AttributesCustomDataInterface|null
     */
    public function process(ProductAttributeInterface $attribute, int $websiteId): ?AttributesCustomDataInterface
    {
        $values = [];
        $result = null;
        try {
            $categories = $this->categoryManagement->getTree($websiteId);
        } catch (\Exception $e) {
            $categories = [];
        }
        foreach ($categories as $category) {
            $values = array_merge($values, $this->getCategoriesData($category));
        }
        if ($type = $this->resolveType($attribute, $values)) {
            $result = $this->getCustomDataObject()
                ->setAttributeCode($attribute->getAttributeCode())
                ->setFrontendLabel((string) $attribute->getDefaultFrontendLabel())
                ->setValues($values)
                ->setType(AttributesCustomDataInterface::ATTRIBUTE_TYPE_COLLECTION)
                ->setCanMulti(true)
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
        return $attributeCode === 'category_ids';
    }

    /**
     * @param CategoryTreeInterface $category
     * @return array
     */
    private function getCategoriesData(CategoryTreeInterface $category): array
    {
        if ($childrenData = $category->getChildrenData()) {
            $children = $this->recursiveCategoriesFilter($childrenData);
            return [
                [
                    'value' => $category->getId(),
                    'label' => $category->getName(),
                ],
                ...$this->unwrapCategories($children),
            ];
        }

        return $this->unwrapCategories([$category]);
    }

    /**
     * @param array $tree
     * @param array $data
     * @param string|null $prefix
     * @return array
     */
    private function unwrapCategories(array $tree, array $data = [], ?string $prefix = null): array
    {
        if (!$tree) {
            return [];
        }
        foreach ($tree as $value) {
            $name = (null !== $prefix ? $prefix .' / ' : '') . $value->getName();
            $data[] = [
                'value' => $value->getId(),
                'label' => $name,
            ];
            if (isset($value) && $value->getChildrenData()) {
                $data = $this->unwrapCategories($value->getChildrenData(), $data, $name);
            }
        }

        return $data;
    }

    /**
     * @param array|null $categories
     * @return array
     */
    private function recursiveCategoriesFilter(?array $categories): array
    {
        if (!$categories) {
            return [];
        }
        array_walk($categories, function (CategoryTreeInterface &$value, int $key) use ($categories): void {
            if (!$value->getId() || !$value->getIsActive()) {
                unset($categories[$key]);
                return;
            }
            $value->setChildrenData($this->recursiveCategoriesFilter($value->getChildrenData()));
        });

        return $categories;
    }
}
