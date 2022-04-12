<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api;

/**
 * @api
 */
interface ConfigProviderInterface
{
    /**
     * Retrieve shipping settings
     *
     * @param int $websiteId
     *
     * @return \Calcurates\ModuleMagento\Api\Data\ConfigDataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     */
    public function getSettings(int $websiteId = null);
}
