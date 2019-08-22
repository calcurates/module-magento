<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Config;

use Calcurates\ModuleMagento\Api\Data\ConfigDataInterface;
use Magento\Framework\DataObject;

class Data extends DataObject implements ConfigDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBaseCurrency()
    {
        return $this->_getData(self::BASE_CURRENCY);
    }

    /**
     * {@inheritdoc}
     */
    public function getWeightUnit()
    {
        return $this->_getData(self::WEIGHT_UNIT);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimezone()
    {
        return $this->_getData(self::TIMEZONE);
    }
}
