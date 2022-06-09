<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Source;

use Calcurates\ModuleMagento\Api\Source\OriginsManagementInterface;
use Calcurates\ModuleMagento\Api\Source\SourceRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

class OriginsManagement implements OriginsManagementInterface
{
    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var SortOrder
     */
    private $sortOrder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * OriginsManagement constructor.
     * @param SourceRepositoryInterface $sourceRepository
     * @param ObjectManagerInterface $objectManager
     * @param ModuleManager $moduleManager
     * @param SortOrder $sortOrder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        ObjectManagerInterface $objectManager,
        ModuleManager $moduleManager,
        SortOrder $sortOrder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        WebsiteRepositoryInterface $websiteRepository
    ) {
        $this->websiteRepository = $websiteRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrder = $sortOrder;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->sourceRepository = $sourceRepository;
    }

    /**
     * @param int|null $websiteId
     * @return mixed[]
     */
    public function getOrigins($websiteId = null): array
    {
        $result = [];
        if (!$websiteId) {
            throw new LocalizedException(__('Target WebsiteId is not specified'));
        }

        $sources = $this->sourceRepository->getList()->getItems();

        if ($this->moduleManager->isEnabled('Magento_Inventory')) {
            $stockInventoryRepository = $this->objectManager
                ->get(\Magento\InventoryApi\Api\StockRepositoryInterface::class);
            $stocks = $stockInventoryRepository->getList()->getItems();

            $stockSourceLinksCommand = $this->objectManager
                ->get(\Magento\InventoryApi\Api\GetStockSourceLinksInterface::class);
            $this->sortOrder
                ->setField("stock_id")
                ->setDirection("ASC");
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $searchCriteria->setSortOrders([$this->sortOrder]);
            $stockSourceLinks = $stockSourceLinksCommand->execute($searchCriteria)->getItems();
        } else {
            $stocks = [];
            $stockSourceLinks = [];
        }
        $websites = $this->websiteRepository->getList();

        $currentWebsiteCode = null;
        foreach ($websites as $website) {
            if ((int) $website->getWebsiteId() === $websiteId) {
                $currentWebsiteCode = $website['code'];
                break;
            }
        }
        if (!$currentWebsiteCode) {
            throw new LocalizedException(__('Cant\'t find current website'));
        }
        $isWebsiteAvailable = static function (string $sourceCode) use ($stocks, $stockSourceLinks, $currentWebsiteCode): bool {
            foreach ($stockSourceLinks as $stockSourceLink) {
                if ($stockSourceLink->getSourceCode() === $sourceCode) {
                    foreach ($stocks as $stock) {
                        if ($stock->getStockId() === $stockSourceLink->getStockId()) {
                            foreach ($stock->getExtensionAttributes()->getSalesChannels() as $stockChannels) {
                                if ('website' === $stockChannels->getType()
                                    && $currentWebsiteCode === $stockChannels->getCode()
                                ) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
            return false;
        };
        foreach ($sources as $source) {
            if (!$source->isEnabled() || !$isWebsiteAvailable($source->getSourceCode())) {
                continue;
            }
            $result[] = [
                'name' => $source->getName(),
                'description' => $source->getDescription(),
                'country' => $source->getCountryId(),
                'regionCode' => $source->getRegionCode(),
                'regionName' => $source->getRegion(),
                'city' => $source->getCity(),
                'addressLine1' => $source->getStreet(),
                'postalCode' => $source->getPostcode(),
                'orderEmail' => $source->getEmail(),
                'contactName' => $source->getContactName(),
                'contactPhone' => $source->getPhone(),
                'code' => $source->getSourceCode(),
            ];
        }
        return $result;
    }
}
