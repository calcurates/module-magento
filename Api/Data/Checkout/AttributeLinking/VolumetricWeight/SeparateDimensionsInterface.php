<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\Data\Checkout\AttributeLinking\VolumetricWeight;

interface SeparateDimensionsInterface
{
    /**
     * @param int $height
     *
     * @return SeparateDimensionsInterface
     */
    public function setHeight($height);

    /**
     * @param int $width
     *
     * @return SeparateDimensionsInterface
     */
    public function setWidth($width);

    /**
     * @param int $length
     *
     * @return SeparateDimensionsInterface
     */
    public function setLength($length);

    /**
     * @return int
     */
    public function getHeight();

    /**
     * @return int
     */
    public function getWidth();

    /**
     * @return int
     */
    public function getLength();
}
