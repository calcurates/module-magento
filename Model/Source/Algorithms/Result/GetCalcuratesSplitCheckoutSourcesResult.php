<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Source\Algorithms\Result;

use Calcurates\ModuleMagento\Model\Source\SourceServiceContext;
use Magento\Framework\ObjectManagerInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterfaceFactory;
use Magento\InventorySourceSelectionApi\Model\GetInStockSourceItemsBySkusAndSortedSource;
use Magento\InventorySourceSelectionApi\Model\GetSourceItemQtyAvailableInterface;

class GetCalcuratesSplitCheckoutSourcesResult
{
    /**
     * @var SourceSelectionItemInterfaceFactory
     */
    private $sourceSelectionItemFactory;

    /**
     * @var SourceSelectionResultInterfaceFactory
     */
    private $sourceSelectionResultFactory;

    /**
     * @var GetInStockSourceItemsBySkusAndSortedSource
     */
    private $getInStockSourceItemsBySkusAndSortedSource;

    /**
     * @var GetSourceItemQtyAvailableInterface
     */
    private $getSourceItemQtyAvailable;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param SourceServiceContext $sourceServiceContext
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        SourceServiceContext $sourceServiceContext
    ) {
        if ($sourceServiceContext->isInventoryEnabled() && $sourceServiceContext->isSourceSelectionEnabled()) {
            $this->sourceSelectionItemFactory = $objectManager->get(SourceSelectionItemInterfaceFactory::class);
            $this->sourceSelectionResultFactory = $objectManager->get(SourceSelectionResultInterfaceFactory::class);
            $this->getInStockSourceItemsBySkusAndSortedSource = $objectManager->get(
                GetInStockSourceItemsBySkusAndSortedSource::class
            );
            $this->getSourceItemQtyAvailable = $objectManager->get(GetSourceItemQtyAvailableInterface::class);
        }
    }

    /**
     * @param InventoryRequestInterface $inventoryRequest
     * @param array $sortedSources
     * @param array $mainSourceCodesForSkus
     * @return SourceSelectionResultInterface
     */
    public function execute(
        InventoryRequestInterface $inventoryRequest,
        array $sortedSources,
        array $mainSourceCodesForSkus = []
    ): SourceSelectionResultInterface {
        $sourceItemSelections = [];

        $itemsTdDeliver = [];
        foreach ($inventoryRequest->getItems() as $item) {
            $normalizedSku = $this->normalizeSku($item->getSku());
            $itemsTdDeliver[$normalizedSku] = $item->getQty();
        }

        $sortedSourceCodes = [];
        foreach ($sortedSources as $sortedSource) {
            $sortedSourceCodes[] = $sortedSource->getSourceCode();
        }

        $sourceItems =
            $this->getInStockSourceItemsBySkusAndSortedSource->execute(
                array_keys($itemsTdDeliver),
                $sortedSourceCodes
            );

        foreach ($sourceItems as $sourceItem) {
            $normalizedSku = $this->normalizeSku($sourceItem->getSku());
            $sourceItemQtyAvailable = $this->getSourceItemQtyAvailable->execute($sourceItem);
            $qtyToDeduct = 0.0;
            if ($sourceItem->getSourceCode() === $mainSourceCodesForSkus[$normalizedSku]) {
                $qtyToDeduct = $itemsTdDeliver[$normalizedSku];
                $itemsTdDeliver[$normalizedSku] -= min($sourceItemQtyAvailable, $itemsTdDeliver[$normalizedSku] ?? 0.0);
            }

            $sourceItemSelections[] = $this->sourceSelectionItemFactory->create(
                [
                    'sourceCode' => $sourceItem->getSourceCode(),
                    'sku' => $sourceItem->getSku(),
                    'qtyToDeduct' => $qtyToDeduct,
                    'qtyAvailable' => $sourceItemQtyAvailable
                ]
            );
        }

        $isShippable = true;
        foreach ($itemsTdDeliver as $itemToDeliver) {
            if (!$this->isZero($itemToDeliver)) {
                $isShippable = false;
                break;
            }
        }

        return $this->sourceSelectionResultFactory->create(
            [
                'sourceItemSelections' => $sourceItemSelections,
                'isShippable' => $isShippable
            ]
        );
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

    /**
     * Compare float number with some epsilon
     *
     * @param float $floatNumber
     * @return bool
     */
    private function isZero(float $floatNumber): bool
    {
        return $floatNumber < 0.0000001;
    }
}
