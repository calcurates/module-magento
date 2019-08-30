<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Catalog\Product;

use Calcurates\ModuleMagento\Api\Catalog\Product\ProductAttributesListInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

class AttributesList implements ProductAttributesListInterface
{

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $eavAttributeRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Attribute\CustomDataFactory
     */
    private $customDataFactory;

    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository,
        \Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributeCustomDataInterfaceFactory $customDataFactory
    ) {
        $this->eavAttributeRepository = $eavAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customDataFactory = $customDataFactory;
    }

    /**
     * @return \Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributeCustomDataInterface[]
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItems()
    {
        /** @var \Magento\Framework\Api\SearchCriteria $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ProductAttributeInterface::IS_VISIBLE, true)
            ->addFilter(ProductAttributeInterface::ATTRIBUTE_ID, 1, 'gteq')
            ->create();

        $attributesItems = $this->eavAttributeRepository->getList(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteria
        )->getItems();
        $result = [];

        foreach ($attributesItems as $attributesItem) {
            $result[] = $this->getCustomDataObject()
                ->setAttributeId($attributesItem->getAttributeId())
                ->setAttributeCode($attributesItem->getAttributeCode())
                ->setFrontendLabel($attributesItem->getDefaultFrontendLabel());
        }

        return $result;
    }

    /**
     * @return \Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributeCustomDataInterface
     */
    private function getCustomDataObject()
    {
        return $this->customDataFactory->create();
    }
}
