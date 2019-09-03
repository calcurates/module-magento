<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Checkout\AttributeLinking;

use Calcurates\ModuleMagento\Api\Data\Checkout\AttributeLinking\VolumetricWeightInterface;
use Calcurates\ModuleMagento\Api\Data\Checkout\AttributeLinking\VolumetricWeight\SeparateDimensionsInterface;

class VolumetricWeight extends \Magento\Framework\DataObject implements VolumetricWeightInterface
{
    /**
     * Add properties of SeparateDimensionsInterface if separate_dimensions is set.
     *
     * {@inheritDoc}
     */
    public function getData($key = '', $index = null)
    {
        $data = parent::getData($key, $index);

        if ($key === '' && \is_array($data) && $this->getSeparateDimensions() !== null) {
            $data[self::SEPARATE_DIMENSIONS] = $this->getSeparateDimensions()->getData();
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function setSeparateDimensions(SeparateDimensionsInterface $separateDimensions)
    {
        $this->setData(self::SEPARATE_DIMENSIONS, $separateDimensions);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setVolume($volume)
    {
        $this->setData(self::VOLUME, $volume);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setVolumetricWeight($volumetricWeight)
    {
        $this->setData(self::VOLUMETRIC_WEIGHT, $volumetricWeight);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSeparateDimensions()
    {
        return $this->_getData(self::SEPARATE_DIMENSIONS);
    }

    /**
     * @inheritDoc
     */
    public function getVolume()
    {
        return $this->_getData(self::VOLUME);
    }

    /**
     * @inheritDoc
     */
    public function getVolumetricWeight()
    {
        return $this->_getData(self::VOLUMETRIC_WEIGHT);
    }
}
