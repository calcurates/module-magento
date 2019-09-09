<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\System\Checkout\Attributes;

use Magento\Eav\Api\Data\AttributeInterface;

class Process
{
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function switchToCode($data)
    {
        $allAttributes = [];

        $this->processArray($data, $allAttributes);
        $this->prepareAttributesData($allAttributes);
        $this->processArray($data, $allAttributes);

        return $data;
    }

    /**
     * @param array $data
     * @param array $linkArray
     */
    private function processArray(&$data, &$linkArray)
    {
        foreach ($data as $key => &$value) {
            if ($value === null) {
                unset($data[$key]);

                continue;
            }

            if (\is_array($value)) {
                $this->processArray($value, $linkArray);

                continue;
            }

            if ((int)$value && isset($linkArray[$value][AttributeInterface::ATTRIBUTE_CODE])) {
                $value = $linkArray[$value][AttributeInterface::ATTRIBUTE_CODE];

                continue;
            }

            $linkArray[$value] = null;
        }
    }

    private function prepareAttributesData(&$attributes)
    {
        $allAttributes = $this->getAttributeCollection()->addFieldToFilter(
            AttributeInterface::ATTRIBUTE_ID,
            ['in' => \array_keys($attributes)]
        )->getData();

        foreach ($allAttributes as $key => $data) {
            $attributes[$data[AttributeInterface::ATTRIBUTE_ID]] = $data;
        }
    }

    /**
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    private function getAttributeCollection()
    {
        return $this->collectionFactory->create();
    }
}
