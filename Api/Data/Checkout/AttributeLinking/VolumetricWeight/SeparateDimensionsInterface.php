<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\Data\Checkout\AttributeLinking\VolumetricWeight;

/**
 * @api
 */
interface SeparateDimensionsInterface
{
    /**
     * @param float $height
     *
     * @return SeparateDimensionsInterface
     */
    public function setHeight($height);

    /**
     * @param float $width
     *
     * @return SeparateDimensionsInterface
     */
    public function setWidth($width);

    /**
     * @param float $length
     *
     * @return SeparateDimensionsInterface
     */
    public function setLength($length);

    /**
     * @return float
     */
    public function getHeight();

    /**
     * @return float
     */
    public function getWidth();

    /**
     * @return float
     */
    public function getLength();

    /**
     * @return bool
     */
    public function isEmpty();
}
