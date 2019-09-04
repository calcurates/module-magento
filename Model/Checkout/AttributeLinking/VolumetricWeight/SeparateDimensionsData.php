<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Checkout\AttributeLinking\VolumetricWeight;

use Calcurates\ModuleMagento\Api\Data\Checkout\AttributeLinking\VolumetricWeight\SeparateDimensionsInterface;

class SeparateDimensionsData implements SeparateDimensionsInterface
{
    /**
     * @var int
     */
    private $height;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $length;

    /**
     * @param array $data
     *
     * @return SeparateDimensionsData
     */
    public function setData($data)
    {
        $this->setHeight(isset($data['height']) ? $data['height'] : null);
        $this->setWidth(isset($data['width']) ? $data['width'] : null);
        $this->setLength(isset($data['length']) ? $data['length'] : null);

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'height' => $this->getHeight(),
            'width' => $this->getWidth(),
            'length' => $this->getLength(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function isEmpty()
    {
        return !$this->getHeight() && !$this->getWidth() && !$this->getLength();
    }

    /**
     * @inheritDoc
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @inheritDoc
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @inheritDoc
     */
    public function getLength()
    {
        return $this->length;
    }
}
