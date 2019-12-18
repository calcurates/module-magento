<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\System\Checkout\Attributes;

use Calcurates\ModuleMagento\Api\Data\Checkout\AttributeLinking\DimensionsInterface as DataDimensionsInterface;
use Calcurates\ModuleMagento\Api\System\Checkout\Attributes\DimensionsInterface;
use Calcurates\ModuleMagento\Model\Config;
use Magento\Catalog\Model\Attribute\Config\Data;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeList;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;

class Dimensions implements DimensionsInterface
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
     * @var Process
     */
    private $processAttributes;

    /**
     * @var CacheTypeList
     */
    private $cacheTypeList;

    /**
     * @var Data
     */
    private $attributeConfig;

    public function __construct(
        WriterInterface $scopeConfig,
        SerializerInterface $serializer,
        Process $processAttributes,
        CacheTypeList $cacheTypeList,
        Data $attributeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->processAttributes = $processAttributes;
        $this->cacheTypeList = $cacheTypeList;
        $this->attributeConfig = $attributeConfig;
    }

    /**
     * @inheritDoc
     */
    public function link(DataDimensionsInterface $attributes, $websiteId)
    {
        $this->validate($attributes);

        $data = [
            'volume' => [
                'volume' => $attributes->getVolume(),
            ],
            'volumetricWeight' => [
                'volumetricWeight' => $attributes->getVolumetricWeight(),
            ],
            'separateDimensions' => $attributes->getSeparateDimensions() !== null
                ? $attributes->getSeparateDimensions()->getData() : null
        ];

        $data = $this->processAttributes->switchToCode($data);
        $data = $this->serializer->serialize($data);

        $this->scopeConfig->save(
            Config::CONFIG_GROUP.Config::CONFIG_ATTRIBUTES_DIMENSIONS,
            $data,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );

        $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
        $this->attributeConfig->reset();
    }

    /**
     * @param DataDimensionsInterface $attributes
     *
     * @throws LocalizedException
     */
    private function validate(DataDimensionsInterface $attributes)
    {
        $separateDimensions = $attributes->getSeparateDimensions();

        if ($separateDimensions !== null
            && !$separateDimensions->isEmpty() // SeparateDimensionsData::class
            && (!$separateDimensions->getLength()
                || !$separateDimensions->getWidth()
                || !$separateDimensions->getHeight())
        ) {
            throw new LocalizedException(
                __('separate_dimensions is set, but one of its properties has invalid value.')
            );
        }
    }
}
