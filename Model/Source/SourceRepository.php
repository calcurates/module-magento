<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Source;

use Calcurates\ModuleMagento\Api\Source\Data\SourceInterface;
use Calcurates\ModuleMagento\Api\Source\Data\SourceInterfaceFactory;
use Calcurates\ModuleMagento\Api\Source\Data\SourceSearchResultsInterface;
use Calcurates\ModuleMagento\Api\Source\Data\SourceSearchResultsInterfaceFactory;
use Calcurates\ModuleMagento\Api\Source\SourceRepositoryInterface;
use Magento\Directory\Model\ResourceModel\Region\Collection as RegionCollection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Inventory\Model\Source\Command\GetListInterface;

class SourceRepository implements SourceRepositoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var SourceSearchResultsInterfaceFactory
     */
    private $sourceSearchResultsFactory;

    /**
     * @var SourceInterfaceFactory
     */
    private $sourceFactory;

    /**
     * @var RegionCollectionFactory
     */
    private $regionCollectionFactory;

    public function __construct(
        ObjectManagerInterface $objectManager,
        SourceSearchResultsInterfaceFactory $sourceSearchResultsFactory,
        SourceInterfaceFactory $sourceFactory,
        RegionCollectionFactory $regionCollectionFactory
    ) {
        $this->objectManager = $objectManager;
        $this->sourceSearchResultsFactory = $sourceSearchResultsFactory;
        $this->sourceFactory = $sourceFactory;
        $this->regionCollectionFactory = $regionCollectionFactory;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @return \Calcurates\ModuleMagento\Api\Source\Data\SourceSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null
    ): \Calcurates\ModuleMagento\Api\Source\Data\SourceSearchResultsInterface {
        /** @var SourceSearchResultsInterface $searchResult */
        $searchResult = $this->sourceSearchResultsFactory->create();

        if (!interface_exists(GetListInterface::class)) {
            if (null !== $searchCriteria) {
                $searchResult->setSearchCriteria($searchCriteria);
            }
            $searchResult->setTotalCount(0);
            $searchResult->setItems([]);
            return $searchResult;
        }

        /** @var GetListInterface $commandGetList */
        $commandGetList = $this->objectManager->get(GetListInterface::class);
        $originalSearchResult = $commandGetList->execute($searchCriteria);
        $searchResult->setSearchCriteria($originalSearchResult->getSearchCriteria());
        $searchResult->setTotalCount($originalSearchResult->getTotalCount());

        $items = $this->prepareItems($originalSearchResult->getItems());
        $searchResult->setItems($items);

        return $searchResult;
    }

    /**
     * @param \Magento\InventoryApi\Api\Data\SourceInterface[] $items
     *
     * @return \Calcurates\ModuleMagento\Api\Source\Data\SourceInterface[]
     */
    private function prepareItems(array $items)
    {
        $regionIds = [];
        foreach ($items as $item) {
            $regionId = $item->getRegionId();
            if (empty($regionId)) {
                continue;
            }
            $regionIds[$regionId] = $regionId;
        }

        $hasRegions = (bool)count($regionIds);
        if ($hasRegions) {
            $regionCollection = $this->regionCollectionFactory->create();
            /** @var RegionCollection $regionCollection */
            $regionCollection->addFieldToFilter('main_table.region_id', array_values($regionIds));
        }

        // load regions
        $resultItems = [];
        foreach ($items as $item) {
            /** @var SourceInterface $resultItem */
            $resultItem = $this->sourceFactory->create(['data' => $item->getData()]);
            if ($hasRegions) {
                $region = $regionCollection->getItemById($item->getRegionId());
                if ($region) {
                    $resultItem->setRegionCode($region->getCode());
                    if (!$resultItem->getRegion()) {
                        $resultItem->setRegion($region->getDefaultName());
                    }
                }
            }
            $resultItems[] = $resultItem;
        }

        return $resultItems;
    }
}
