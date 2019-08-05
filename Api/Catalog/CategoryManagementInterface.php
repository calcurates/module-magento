<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_Integration
 */

namespace Calcurates\Integration\Api\Catalog;

/**
 * @api
 */
interface CategoryManagementInterface
{
    /**
     * Retrieve list of categories
     *
     * @param int $websiteId
     * @param int $depth
     *
     * @return \Magento\Catalog\Api\Data\CategoryTreeInterface containing Tree objects
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     */
    public function getTree($websiteId = null, $depth = null);
}
