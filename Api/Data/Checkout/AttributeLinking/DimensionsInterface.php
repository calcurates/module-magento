<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\Data\Checkout\AttributeLinking;

/**
 * @api
 */
interface DimensionsInterface
{
    const SEPARATE_DIMENSIONS = 'separate_dimensions';
    const VOLUME = 'volume';
    const VOLUMETRIC_WEIGHT = 'volumetric_weight';

    /**
     * @param \Calcurates\ModuleMagento\Api\Data\Checkout\AttributeLinking\VolumetricWeight\SeparateDimensionsInterface $separateDimensions
     *
     * @return DimensionsInterface
     */
    public function setSeparateDimensions(VolumetricWeight\SeparateDimensionsInterface $separateDimensions);

    /**
     * @param int $volume
     *
     * @return DimensionsInterface
     */
    public function setVolume($volume);

    /**
     * @param int $volumetricWeight
     *
     * @return DimensionsInterface
     */
    public function setVolumetricWeight($volumetricWeight);

    /**
     * @return \Calcurates\ModuleMagento\Api\Data\Checkout\AttributeLinking\VolumetricWeight\SeparateDimensionsInterface|null
     */
    public function getSeparateDimensions();

    /**
     * @return int|null
     */
    public function getVolume();

    /**
     * @return int|null
     */
    public function getVolumetricWeight();
}
