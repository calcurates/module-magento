<?php

namespace Calcurates\ModuleMagento\Model\Source\Algorithms\Result;

use Calcurates\ModuleMagento\Model\Source\SourceServiceContext;
use Magento\Framework\ObjectManagerInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterfaceFactory;
use Magento\InventorySourceSelectionApi\Model\GetInStockSourceItemsBySkusAndSortedSource;
use Magento\InventorySourceSelectionApi\Model\GetSourceItemQtyAvailableInterface;

class GetCalcuratesSortedSourcesResult
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
     * GetCalcuratesSortedSourcesResult constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        if (SourceServiceContext::doesSourceExist()) {
            $this->sourceSelectionItemFactory = $objectManager->get(SourceSelectionItemInterfaceFactory::class);
            $this->sourceSelectionResultFactory = $objectManager->get(SourceSelectionResultInterfaceFactory::class);
            $this->getInStockSourceItemsBySkusAndSortedSource = $objectManager->get(
                GetInStockSourceItemsBySkusAndSortedSource::class
            );
            $this->getSourceItemQtyAvailable = $objectManager->get(GetSourceItemQtyAvailableInterface::class);
        }
    }

    /**
     * Compare float number with some epsilon
     *
     * @param float $floatNumber
     *
     * @return bool
     */
    private function isZero($floatNumber)
    {
        return $floatNumber < 0.0000001;
    }

    /**
     * Generate default result for priority based algorithms
     *
     * @param InventoryRequestInterface $inventoryRequest
     * @param array $sortedSources
     * @param array $mainSourceCodesForSkus
     * @return SourceSelectionResultInterface
     */
    public function execute(
        InventoryRequestInterface $inventoryRequest,
        array $sortedSources,
        array $mainSourceCodesForSkus = []
    ) {
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

        // sort source items, where calcurate-recommended sourceCodes are first
        usort($sourceItems,
            function (SourceItemInterface $itemA, SourceItemInterface $itemB) use ($mainSourceCodesForSkus) {
                $normalizedSkuA = $this->normalizeSku($itemA->getSku());
                $normalizedSkuB = $this->normalizeSku($itemB->getSku());
                if ($normalizedSkuA == $normalizedSkuB) {
                    return 0;
                }

                if ($mainSourceCodesForSkus[$normalizedSkuA] == $itemA->getSourceCode()) {
                    return -1;
                }

                if ($mainSourceCodesForSkus[$normalizedSkuB] == $itemB->getSourceCode()) {
                    return 1;
                }

                return 0;
            }
        );

        foreach ($sourceItems as $sourceItem) {
            $normalizedSku = $this->normalizeSku($sourceItem->getSku());
            $sourceItemQtyAvailable = $this->getSourceItemQtyAvailable->execute($sourceItem);
            $qtyToDeduct = min($sourceItemQtyAvailable, $itemsTdDeliver[$normalizedSku] ?? 0.0);

            $sourceItemSelections[] = $this->sourceSelectionItemFactory->create(
                [
                    'sourceCode' => $sourceItem->getSourceCode(),
                    'sku' => $sourceItem->getSku(),
                    'qtyToDeduct' => $qtyToDeduct,
                    'qtyAvailable' => $sourceItemQtyAvailable
                ]
            );

            $itemsTdDeliver[$normalizedSku] -= $qtyToDeduct;
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
    private function normalizeSku($sku)
    {
        return mb_convert_case($sku, MB_CASE_LOWER, 'UTF-8');
    }
}
