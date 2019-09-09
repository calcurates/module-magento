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

    /**
     * @var Process
     */
    private $processAttributes;

    public function __construct(
        WriterInterface $scopeConfig,
        SerializerInterface $serializer,
        Process $processAttributes
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->processAttributes = $processAttributes;
    }

    /**
     * @inheritDoc
     */
    public function link(DataVolumetricWeightInterface $attributes, $websiteId)
    {
        $this->validate($attributes);

        $data = [
            'volumetricWeight' => [
                'volume' => [
                    'volume' => $attributes->getVolume(),
                ],
                'volumetricWeight' => [
                    'volumetricWeight' => $attributes->getVolumetricWeight(),
                ],
                'separateDimensions' => $attributes->getSeparateDimensions()->getData()
            ]
        ];

        $data = $this->processAttributes->switchToCode($data);
        $data = $this->serializer->serialize($data);

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
        $separateDimensions = $attributes->getSeparateDimensions();

        if ($separateDimensions !== null
            && !$separateDimensions->isEmpty()
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
