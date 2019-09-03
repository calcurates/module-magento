<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\System\Checkout\Attributes;

use Calcurates\ModuleMagento\Api\Data\Checkout\AttributeLinking\VolumetricWeightInterface as DataVolumetricWeightInterface;
use Calcurates\ModuleMagento\Api\System\Checkout\Attributes\VolumetricWeightInterface;
use Calcurates\ModuleMagento\Model\Config;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;

class VolumetricWeight implements VolumetricWeightInterface
{
    /**
     * @var WriterInterface
     */
    private $scopeConfig;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        WriterInterface $scopeConfig,
        SerializerInterface $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function link(DataVolumetricWeightInterface $attributes, $websiteId)
    {
        $this->validate($attributes);

        $data = $this->serializer->serialize($attributes->getData());

        $this->scopeConfig->save(
            Config::CONFIG_GROUP.Config::CONFIG_ATTRIBUTES_VOLUMETRIC_WEIGHT,
            $data,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
    }

    /**
     * @param DataVolumetricWeightInterface $attributes
     *
     * @throws LocalizedException
     */
    private function validate(DataVolumetricWeightInterface $attributes)
    {
        if ($attributes->getSeparateDimensions() !== null
            && !($attributes->getSeparateDimensions()->getLength()
                && $attributes->getSeparateDimensions()->getWidth()
                && $attributes->getSeparateDimensions()->getHeight())
        ) {
            throw new LocalizedException(
                __('separate_dimensions is set, but one of its properties has invalid value.')
            );
        }
    }
}
