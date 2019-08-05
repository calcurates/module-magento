<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_Integration
 */

namespace Calcurates\Integration\Api;

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
     * @return \Calcurates\Integration\Api\Data\ConfigDataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     */
    public function getSettings($websiteId = null);
}
