<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\Source;

interface SourceRepositoryInterface
{
    /**
     * Find Sources by SearchCriteria
     * SearchCriteria is not required because load all stocks is useful case
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @return \Calcurates\ModuleMagento\Api\Source\Data\SourceSearchResultsInterface
     */
    public function getList(
        ?\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null
    ): \Calcurates\ModuleMagento\Api\Source\Data\SourceSearchResultsInterface;
}
