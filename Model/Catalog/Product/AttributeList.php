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
use Magento\Framework\Api\SearchCriteria;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Calcurates\ModuleMagento\Api\Data\Catalog\Product\Attribute\ProcessorInterface;

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
     * @var array
     */
    private $attributeProcessors = [];

    /**
     * AttributeList constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $eavAttributeRepository
     * @param array $attributeProcessors
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $eavAttributeRepository,
        array $attributeProcessors = []
    ) {
        $this->attributeProcessors = $attributeProcessors;
        $this->eavAttributeRepository = $eavAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param int $websiteId
     * @return array
     */
    public function getItems(int $websiteId): array
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
            $item = $defaultProcessor = null;
            foreach ($this->attributeProcessors as $key => $attributeProcessor) {
                if ($key === "default") {
                    $defaultProcessor = $attributeProcessor;
                    continue;
                }
                if ($attributeProcessor instanceof ProcessorInterface) {
                    if ($attributeProcessor->canProcess($attributesItem->getAttributeCode())) {
                        $item = $attributeProcessor->process($attributesItem, $websiteId);
                        if ($item) {
                            $result[] = $item;
                            break;
                        }
                    }
                }
            }
            if ($defaultProcessor && !$item) {
                $item = $defaultProcessor->process($attributesItem, $websiteId);
                if ($item) {
                    $result[] = $item;
                }
            }
        }

        return $result;
    }
}
