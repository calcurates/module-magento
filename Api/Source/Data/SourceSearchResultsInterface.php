<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\Source\Data;

interface SourceSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get sources list
     *
     * @return \Calcurates\ModuleMagento\Api\Source\Data\SourceInterface[]
     */
    public function getItems();

    /**
     * Set sources list
     *
     * @param \Calcurates\ModuleMagento\Api\Source\Data\SourceInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
