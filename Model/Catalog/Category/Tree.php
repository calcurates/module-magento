<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_Integration
 */

namespace Calcurates\Integration\Model\Catalog\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Category\Tree as OriginalTree;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException as NoSuchEntityExceptionAlias;

class Tree extends OriginalTree
{
    /**
     * Bug fix for Magento 2.3.2 where name and product count don't show up when loading tree by category id
     * @see OriginalTree::getNode
     *
     * @param Category $category
     *
     * @return Node
     * @throws LocalizedException
     * @throws NoSuchEntityExceptionAlias
     */
    protected function getNode(Category $category)
    {
        $node = $this->categoryTree->loadNode($category->getId());
        $node->loadChildren();
        $this->prepareCollection();
        $this->categoryTree->addCollectionData($this->categoryCollection);

        return $node;
    }
}
