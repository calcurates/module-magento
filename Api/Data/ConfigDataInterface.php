<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_Integration
 */

namespace Calcurates\Integration\Api\Data;

/**
 * @api
 */
interface ConfigDataInterface
{
    /**#@+
     * Keys for data array
     */
    const BASE_CURRENCY = 'base_currency';
    const WEIGHT_UNIT = 'weight_unit';
    const TIMEZONE = 'timezone';
    /**#@-*/

    /**
     * @return string
     */
    public function getBaseCurrency();

    /**
     * @return string
     */
    public function getWeightUnit();

    /**
     * @return string
     */
    public function getTimezone();
}
