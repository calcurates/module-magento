<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\Data;

/**
 * @api
 */
interface ConfigDataInterface
{
    /**#@+
     * Keys for data array
     */
    public const BASE_CURRENCY = 'base_currency';
    public const WEIGHT_UNIT = 'weight_unit';
    public const TIMEZONE = 'timezone';
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
