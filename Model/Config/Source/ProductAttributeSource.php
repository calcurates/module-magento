<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class ProductAttributeSource implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @param CollectionFactory $attributeCollectionFactory
     */
    public function __construct(CollectionFactory $attributeCollectionFactory)
    {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $attributeCollection = $this->attributeCollectionFactory->create()
            ->addFieldToFilter('frontend_input', ['in' => ['select', 'boolean', 'multiselect']]);
        $attributes = [
            [
                'label' => __('None'),
                'value' => ''
            ]
        ];
        foreach ($attributeCollection->getItems() as $attribute) {
            $attributes[] = [
                'label' => $attribute->getFrontendLabel(),
                'value' => $attribute->getAttributeCode()
            ];
        }
        return $attributes;
    }
}
