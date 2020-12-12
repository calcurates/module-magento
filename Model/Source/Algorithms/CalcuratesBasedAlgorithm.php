<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Source\Algorithms;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Calcurates\ModuleMagento\Model\Source\Algorithms\Result\GetCalcuratesSortedSourcesResult;
use Calcurates\ModuleMagento\Model\Source\SourceServiceContext;
use Magento\Framework\ObjectManagerInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;
use Magento\InventorySourceSelectionApi\Model\SourceSelectionInterface;

// fix for correct working of \Magento\Setup\Module\Di\Code\Reader\FileClassScanner
if (true) {
    if (!interface_exists(\Magento\InventorySourceSelectionApi\Model\SourceSelectionInterface::class)) {
        class_alias(
            \Calcurates\ModuleMagento\Api\Fake\SourceSelectionInterface::class,
            \Magento\InventorySourceSelectionApi\Model\SourceSelectionInterface::class
        );
    }
}


class CalcuratesBasedAlgorithm implements SourceSelectionInterface
{
    /**
     * @var GetCalcuratesSortedSourcesResult
     */
    private $getCalcuratesSortedSourcesResult;

    /**
     * @var GetSourcesAssignedToStockOrderedByPriorityInterface
     */
    private $getSourcesAssignedToStockOrderedByPriority;

    /**
     * @var OrderItemsRetriever
     */
    private $orderItemsRetriever;

    /**
     * CalcuratesBasedAlgorithm constructor.
     * @param GetCalcuratesSortedSourcesResult $getCalcuratesSortedSourcesResult
     * @param ObjectManagerInterface $objectManager
     * @param OrderItemsRetriever $orderItemsRetriever
     * @param SourceServiceContext $sourceServiceContext
     */
    public function __construct(
        GetCalcuratesSortedSourcesResult $getCalcuratesSortedSourcesResult,
        ObjectManagerInterface $objectManager,
        OrderItemsRetriever $orderItemsRetriever,
        SourceServiceContext $sourceServiceContext
    ) {
        $this->getCalcuratesSortedSourcesResult = $getCalcuratesSortedSourcesResult;
        if ($sourceServiceContext->isInventoryEnabled()) {
            $this->getSourcesAssignedToStockOrderedByPriority = $objectManager->get(
                GetSourcesAssignedToStockOrderedByPriorityInterface::class
            );
        }
        $this->orderItemsRetriever = $orderItemsRetriever;
    }

    /**
     * @inheritDoc
     */
    public function execute(InventoryRequestInterface $inventoryRequest): SourceSelectionResultInterface
    {
        $mainSourceCodesForSkus = [];
        // workaround for get order items(because in inventory request we haven't that information)
        $orderItems = $this->orderItemsRetriever->getOrderItems();
        foreach ($orderItems as $orderItem) {
            $normalizedSku = $this->normalizeSku($orderItem->getSku());
            $mainSourceCodesForSkus[$normalizedSku] = $orderItem->getData(
                CustomSalesAttributesInterface::SOURCE_CODE
            );
        }

        $stockId = $inventoryRequest->getStockId();
        $sortedSources = $this->getEnabledSourcesOrderedByPriorityByStockId($stockId);

        return $this->getCalcuratesSortedSourcesResult->execute($inventoryRequest, $sortedSources, $mainSourceCodesForSkus);
    }

    /**
     * Get enabled sources ordered by priority by $stockId
     *
     * @param int $stockId
     * @return array
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getEnabledSourcesOrderedByPriorityByStockId(int $stockId): array
    {
        $sources = $this->getSourcesAssignedToStockOrderedByPriority->execute($stockId);
        $sources = array_filter($sources, function (SourceInterface $source) {
            return $source->isEnabled();
        });
        return $sources;
    }

    /**
     * Convert SKU to lowercase
     *
     * Normalize SKU by converting it to lowercase.
     *
     * @param string $sku
     * @return string
     */
    private function normalizeSku(string $sku): string
    {
        return mb_convert_case($sku, MB_CASE_LOWER, 'UTF-8');
    }
}
