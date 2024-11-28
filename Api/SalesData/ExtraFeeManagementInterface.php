<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\SalesData;

/**
 * @api
 */
interface ExtraFeeManagementInterface
{
    /**
     * Retrieve list of categories
     *
     * @param int $websiteId
     *
     * @return mixed[] containing Extra Fee objects
     */
    public function getFees($websiteId = null);
}
