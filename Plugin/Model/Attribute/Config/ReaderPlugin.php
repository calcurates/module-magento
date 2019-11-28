<?php

namespace Calcurates\ModuleMagento\Plugin\Model\Attribute\Config;

use Magento\Eav\Api\Data\AttributeInterface;

/**
 * Class ReaderPlugin
 *
 * @package Calcurates\ModuleMagento\Plugin\Model\Attribute\Config
 */
class ReaderPlugin
{
    /**
     * @var \Calcurates\ModuleMagento\Model\Config
     */
    private $config;

    /**
     * ReaderPlugin constructor.
     * @param \Calcurates\ModuleMagento\Model\Config $config
     */
    public function __construct(
        \Calcurates\ModuleMagento\Model\Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param \Magento\Catalog\Model\Attribute\Config\Reader $subject
     * @param array $result
     * @return array
     */
    public function afterRead(\Magento\Catalog\Model\Attribute\Config\Reader $subject, $result)
    {
        $volumetricData = $this->config->getLinkedVolumetricWeightAttributes();
        $attributes = [];
        $quoteAttributes = isset($result['quote_item']) ? $result['quote_item'] : [];

        $this->processAttributesArray($volumetricData, $attributes);
        $this->processCustomAttributes($attributes);

        $result['quote_item'] = \array_merge($quoteAttributes, \array_keys($attributes));

        return $result;
    }

    /**
     * @param array $attributes
     */
    private function processCustomAttributes(&$attributes)
    {
        foreach ($this->config->getCustomAttributes() as $customAttribute) {
            $attributes[$customAttribute] = $customAttribute;
        }
    }

    /**
     * @param array $data
     * @param array $linkArray
     */
    private function processAttributesArray(&$data, &$linkArray)
    {
        foreach ($data as $key => &$value) {
            if (\is_array($value)) {
                $this->processAttributesArray($value, $linkArray);

                continue;
            }

            if ((int)$value && isset($linkArray[$value], $linkArray[$value][AttributeInterface::ATTRIBUTE_CODE])) {
                $value = $linkArray[$value][AttributeInterface::ATTRIBUTE_CODE];

                continue;
            }

            $linkArray[$value] = $value;
        }
    }
}
