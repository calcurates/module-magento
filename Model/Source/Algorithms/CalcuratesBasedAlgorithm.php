<?php

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
use Magento\Sales\Api\OrderItemRepositoryInterface;

if (!SourceServiceContext::doesSourceExist()) {
    class_alias(
        \Calcurates\ModuleMagento\Api\Fake\SourceSelectionInterface::class,
        \Magento\InventorySourceSelectionApi\Model\SourceSelectionInterface::class
    );
}

class CalcuratesBasedAlgorithm implements SourceSelectionInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var GetCalcuratesSortedSourcesResult
     */
    private $getCalcuratesSortedSourcesResult;

    /**
     * @var GetSourcesAssignedToStockOrderedByPriorityInterface
     */
    private $getSourcesAssignedToStockOrderedByPriority;

    /**
     * CalcuratesBasedAlgorithm constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param GetCalcuratesSortedSourcesResult $getCalcuratesSortedSourcesResult
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        OrderItemRepositoryInterface $orderItemRepository,
        GetCalcuratesSortedSourcesResult $getCalcuratesSortedSourcesResult,
        ObjectManagerInterface $objectManager
    ) {
        $this->request = $request;
        $this->orderItemRepository = $orderItemRepository;
        $this->getCalcuratesSortedSourcesResult = $getCalcuratesSortedSourcesResult;
        if (SourceServiceContext::doesSourceExist()) {
            $this->getSourcesAssignedToStockOrderedByPriority = $objectManager->get(
                GetSourcesAssignedToStockOrderedByPriorityInterface::class
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function execute(InventoryRequestInterface $inventoryRequest): SourceSelectionResultInterface
    {
        $requestData = $this->request->getParam('requestData');

        $mainSourceCodesForSkus = [];
        if (!empty($requestData)) {
            foreach ($requestData as $item) {
                $orderItem = $this->orderItemRepository->get($item['orderItem']);
                $normalizedSku = $this->normalizeSku($item['sku']);
                $mainSourceCodesForSkus[$normalizedSku] = $orderItem->getData(
                    CustomSalesAttributesInterface::SOURCE_CODE
                );
            }
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
