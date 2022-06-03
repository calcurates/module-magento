<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\Source;

interface OriginsManagementInterface
{
    /**
     * @param int|null $websiteId
     * @return mixed[] - required to process the Api requests, should not be changed to array
     */
    public function getOrigins($websiteId = null): array;
}
