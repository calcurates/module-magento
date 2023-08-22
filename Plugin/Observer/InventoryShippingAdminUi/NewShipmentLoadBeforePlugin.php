<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */
namespace Calcurates\ModuleMagento\Plugin\Observer\InventoryShippingAdminUi;

use Calcurates\ModuleMagento\Model\Config;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Item;

class NewShipmentLoadBeforePlugin
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var mixed
     */
    private $isWebsiteInMultiSourceMode;

    /**
     * @var mixed
     */
    private $orderSourceManageable;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var mixed
     */
    private $getStockItemConfiguration;

    /**
     * @var mixed
     */
    private $getSkuFromOrderItem;

    /**
     * @var array
     */
    private $sources = [];

    /**
     * @var mixed
     */
    private $getSourcesByOrderIdSkuAndQty;

    /**
     * @var mixed
     */
    private $stockByWebsiteIdResolver;

    /**
     * NewShipmentLoadBeforePlugin constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param Config $config
     * @param ModuleManager $moduleManager
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Config $config,
        ModuleManager $moduleManager,
        ObjectManagerInterface $objectManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->config = $config;
        if ($moduleManager->isEnabled('Magento_InventoryShippingAdminUi')
            && $moduleManager->isEnabled('Magento_InventorySalesApi')
        ) {
            $this->isWebsiteInMultiSourceMode = $objectManager
                ->get(\Magento\InventoryShippingAdminUi\Model\IsWebsiteInMultiSourceMode::class);
            $this->stockByWebsiteIdResolver = $objectManager
                ->get(\Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface::class);
            $this->getStockItemConfiguration = $objectManager
                ->get(\Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface::class);
            $this->getSkuFromOrderItem = $objectManager
                ->get(\Magento\InventorySalesApi\Model\GetSkuFromOrderItemInterface::class);
            $this->getSourcesByOrderIdSkuAndQty = $objectManager
                ->get(\Magento\InventoryShippingAdminUi\Ui\DataProvider\GetSourcesByOrderIdSkuAndQty::class);
            $this->orderSourceManageable = $objectManager
                ->get(\Magento\InventoryShippingAdminUi\Model\IsOrderSourceManageable::class);
        }
    }

    /**
     * @param ObserverInterface $subject
     * @param EventObserver $observer
     * @return array
     */
    public function beforeExecute(ObserverInterface $subject, EventObserver $observer): array
    {
        $request = $observer->getEvent()->getRequest();
        if (!empty($request->getParam('items'))
            && !empty($request->getParam('sourceCode'))) {
            return [$observer];
        }
        try {
            $orderId = $request->getParam('order_id');
            $order = $this->orderRepository->get($orderId);
            if (!$this->orderSourceManageable->execute($order)) {
                return [$observer];
            }
            $websiteId = (int)$order->getStore()->getWebsiteId();
            if ($this->isWebsiteInMultiSourceMode->execute($websiteId)
                && $this->config->isAutomaticSourceSelectionEnabled()
            ) {
                $this->populateDefaultData($orderId, $request);
            }
        } catch (\Exception $e) {
            return [$observer];
        }
        return [$observer];
    }

    /**
     * @param int $orderId
     * @param $request
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function populateDefaultData(int $orderId, $request): void
    {
        $data = [];
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderId);
        $websiteId = $order->getStore()->getWebsiteId();
        $stockId = (int) $this->stockByWebsiteIdResolver->execute((int)$websiteId)->getStockId();
        $sources = [];
        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getIsVirtual()
                || $orderItem->getLockedDoShip()
                || $orderItem->getHasChildren()) {
                continue;
            }
            $item = $orderItem->isDummy(true) ? $orderItem->getParentItem() : $orderItem;
            $qty = $item->getSimpleQtyToShip();
            $qty = $this->castQty($item, $qty);
            $sku = $this->getSkuFromOrderItem->execute($item);
            $itemSources = $this->getSources($orderId, $sku, $qty);
            $data['items'][] = [
                'orderItemId' => $item->getId(),
                'sku' => $sku,
                'qtyToShip' => $qty,
                'sources' => $itemSources,
                'isManageStock' => $this->isManageStock($sku, $stockId)
            ];
            if ($qty) {
                $sources[] = $itemSources;
            }
        }
        if (is_array($this->sources) && $this->sources) {
            $sourceCode = '';
            foreach ($this->sources as $algorithmSourceCode => $algorithmSource) {
                foreach ($sources as $itemsSources) {
                    foreach ($itemsSources as $itemSource) {
                        if (!$sourceCode
                            && array_key_exists('sourceCode', $itemSource)
                            && $itemSource['sourceCode'] == $algorithmSourceCode
                        ) {
                            $sourceCode = $algorithmSourceCode;
                        }
                    }
                }
            }
            if ($sourceCode) {
                $request->setParam('sourceCode', $sourceCode);
            } else {
                $request->setParam('sourceCode', array_key_first($this->sources));
            }
        }

        $request->setParam('items', $data['items']);
    }

    /**
     * Get sources
     *
     * @param int $orderId
     * @param string $sku
     * @param float $qty
     * @return array
     * @throws NoSuchEntityException
     */
    private function getSources(int $orderId, string $sku, float $qty): array
    {
        $sources = $this->getSourcesByOrderIdSkuAndQty->execute($orderId, $sku, $qty);
        foreach ($sources as $source) {
            $this->sources[$source['sourceCode']] = $source['sourceName'];
        }
        return $sources;
    }

    /**
     * @param $itemSku
     * @param $stockId
     * @return bool
     * @throws LocalizedException
     */
    private function isManageStock($itemSku, $stockId)
    {
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($itemSku, $stockId);

        return $stockItemConfiguration->isManageStock();
    }

    /**
     * @param Item $item
     * @param string|int|float $qty
     * @return float|int
     */
    private function castQty(Item $item, $qty)
    {
        if ($item->getIsQtyDecimal()) {
            $qty = (double)$qty;
        } else {
            $qty = (int)$qty;
        }

        return $qty > 0 ? $qty : 0;
    }
}
