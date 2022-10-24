<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Catalog\Category;

use Magento\Catalog\Api\Data\CategoryTreeInterfaceFactory;
use Magento\Catalog\Model\Category\Tree as OriginalTree;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\Tree as TreeResource;
use Magento\Catalog\Model\ResourceModel\Category\TreeFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class Tree extends OriginalTree
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        TreeResource $categoryTree,
        StoreManagerInterface $storeManager,
        Collection $categoryCollection,
        CategoryTreeInterfaceFactory $treeFactory,
        CollectionFactory $collectionFactory,
        TreeFactory $treeResourceFactory = null
    ) {
        parent::__construct(
            $categoryTree,
            $storeManager,
            $categoryCollection,
            $treeFactory,
            $treeResourceFactory
        );
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function prepareCollection()
    {
        if ($this->categoryCollection->isLoaded()) {
            $this->categoryCollection = $this->collectionFactory->create();
        }
        parent::prepareCollection();
    }
}
