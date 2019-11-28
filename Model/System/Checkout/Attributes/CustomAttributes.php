<?php

namespace Calcurates\ModuleMagento\Model\System\Checkout\Attributes;

use Calcurates\ModuleMagento\Api\System\Checkout\Attributes\CustomAttributesInterface;
use Calcurates\ModuleMagento\Model\Config;
use Magento\Catalog\Model\Attribute\Config\Data;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeList;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;

class CustomAttributes implements CustomAttributesInterface
{
    /**
     * @var WriterInterface
     */
    private $scopeConfig;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CacheTypeList
     */
    private $cacheTypeList;

    /**
     * @var Data
     */
    private $attributeConfig;

    /**
     * CustomAttributes constructor.
     * @param WriterInterface $scopeConfig
     * @param SerializerInterface $serializer
     * @param CacheTypeList $cacheTypeList
     * @param Data $attributeConfig
     */
    public function __construct(
        WriterInterface $scopeConfig,
        SerializerInterface $serializer,
        CacheTypeList $cacheTypeList,
        Data $attributeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->cacheTypeList = $cacheTypeList;
        $this->attributeConfig = $attributeConfig;
    }

    /**
     * @param \Calcurates\ModuleMagento\Api\Data\Checkout\AttributeLinking\CustomAttributeInterface[] $attributes
     * @param int $websiteId
     *
     * @return void
     * @throws InputException
     */
    public function save($attributes, $websiteId)
    {
        $attributeCodes = [];
        foreach ($attributes as $attribute) {
            if (empty($attribute->getAttributeCode())) {
                throw InputException::requiredField('attribute_code');
            }
            $attributeCodes[$attribute->getAttributeCode()] = $attribute->getAttributeCode();
        }

        $attributeCodes = array_values($attributeCodes);
        $data = $this->serializer->serialize($attributeCodes);

        $this->scopeConfig->save(
            Config::CONFIG_GROUP.Config::CONFIG_ATTRIBUTES_CUSTOM_ATTRIBUTES,
            $data,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );

        $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
        $this->attributeConfig->reset();
    }
}
