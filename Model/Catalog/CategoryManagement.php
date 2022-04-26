<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Catalog;

use Calcurates\ModuleMagento\Api\Catalog\CategoryManagementInterface;
use Calcurates\ModuleMagento\Model\Catalog\Category\Tree;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryTreeInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class CategoryManagement implements CategoryManagementInterface
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var Tree
     */
    private $categoryTree;

    /**
     * @var CollectionFactory
     */
    private $categoriesFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        Tree $categoryTree,
        CollectionFactory $categoriesFactory,
        StoreManagerInterface $storeManager,
        ScopeResolverInterface $scopeResolver
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryTree = $categoryTree;
        $this->categoriesFactory = $categoriesFactory;
        $this->storeManager = $storeManager;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * {@inheritdoc}
     *
     * @throws LocalizedException
     */
    public function getTree($websiteId = null, $depth = null)
    {
        $rootCategoryId = null;

        if ($websiteId) {
            $rootCategoryId = $this->storeManager->getWebsite($websiteId)
                ->getDefaultGroup()->getRootCategoryId();
        }

        $tree = $this->getTreeByCategoryId($rootCategoryId, $depth);
        $this->filterTree($tree);

        return $tree;
    }

    /**
     * @param int|null $rootCategoryId
     * @param int|null $depth
     *
     * @return CategoryTreeInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getTreeByCategoryId($rootCategoryId = null, $depth = null)
    {
        $category = null;

        if ($rootCategoryId !== null) {
            /** @var Category $category */
            $category = $this->categoryRepository->get($rootCategoryId);
        } elseif ($this->isAdminStore()) {
            $category = $this->getTopLevelCategory();
        }

        return $this->categoryTree->getTree(
            $this->categoryTree->getRootNode($category),
            $depth
        );
    }

    /**
     * Filter out non-active categories
     *
     * @param CategoryTreeInterface $tree
     */
    private function filterTree(CategoryTreeInterface $tree)
    {
        $childrenData = [];

        foreach ($tree->getChildrenData() as $childTree) {
            if ($childTree->getIsActive()) {
                $this->filterTree($childTree);
                $childrenData[] = $childTree;
            }
        }
        $tree->setChildrenData($childrenData);
    }

    /**
     * Check is request use default scope
     *
     * @return bool
     */
    private function isAdminStore()
    {
        return $this->scopeResolver->getScope()->getCode() === \Magento\Store\Model\Store::ADMIN_CODE;
    }

    /**
     * Get top level hidden root category
     *
     * @return Category|DataObject
     */
    private function getTopLevelCategory()
    {
        /** @var Collection $categoriesCollection */
        $categoriesCollection = $this->categoriesFactory->create();

        return $categoriesCollection->addFilter('level', ['eq' => 0])->getFirstItem();
    }
}
