<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */
namespace Calcurates\ModuleMagento\Plugin\Observer\InventoryShippingAdminUi;

use Magento\InventoryShippingAdminUi\Observer\NewShipmentLoadBefore;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryShippingAdminUi\Model\IsWebsiteInMultiSourceMode;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\InventoryShippingAdminUi\Model\IsOrderSourceManageable;
use Calcurates\ModuleMagento\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Model\GetSkuFromOrderItemInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\Sales\Model\Order\Item;
use Magento\InventoryShippingAdminUi\Ui\DataProvider\GetSourcesByOrderIdSkuAndQty;

class NewShipmentLoadBeforePlugin
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var IsWebsiteInMultiSourceMode
     */
    private $isWebsiteInMultiSourceMode;

    /**
     * @var IsOrderSourceManageable
     */
    private $orderSourceManageable;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var GetSkuFromOrderItemInterface
     */
    private $getSkuFromOrderItem;

    /**
     * @var array
     */
    private $sources = [];

    /**
     * @var GetSourcesByOrderIdSkuAndQty
     */
    private $getSourcesByOrderIdSkuAndQty;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    /**
     * NewShipmentLoadBeforePlugin constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param IsWebsiteInMultiSourceMode $isWebsiteInMultiSourceMode
     * @param Config $config
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param GetSkuFromOrderItemInterface $getSkuFromOrderItem
     * @param GetSourcesByOrderIdSkuAndQty|null $getSourcesByOrderIdSkuAndQty
     * @param IsOrderSourceManageable|null $isOrderSourceManageable
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        IsWebsiteInMultiSourceMode $isWebsiteInMultiSourceMode,
        Config $config,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        GetSkuFromOrderItemInterface $getSkuFromOrderItem,
        GetSourcesByOrderIdSkuAndQty $getSourcesByOrderIdSkuAndQty = null,
        IsOrderSourceManageable $isOrderSourceManageable = null
    ) {
        $this->orderRepository = $orderRepository;
        $this->isWebsiteInMultiSourceMode = $isWebsiteInMultiSourceMode;
        $this->config = $config;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->getSkuFromOrderItem = $getSkuFromOrderItem;
        $this->getSourcesByOrderIdSkuAndQty = $getSourcesByOrderIdSkuAndQty ?:
            ObjectManager::getInstance()->get(GetSourcesByOrderIdSkuAndQty::class);
        $this->orderSourceManageable = $isOrderSourceManageable ??
            ObjectManager::getInstance()->get(IsOrderSourceManageable::class);
    }

    /**
     * @param NewShipmentLoadBefore $subject
     * @param EventObserver $observer
     * @return array
     */
    public function beforeExecute(NewShipmentLoadBefore $subject, EventObserver $observer): array
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
            $data['items'][] = [
                'orderItemId' => $item->getId(),
                'sku' => $sku,
                'qtyToShip' => $qty,
                'sources' => $this->getSources($orderId, $sku, $qty),
                'isManageStock' => $this->isManageStock($sku, $stockId)
            ];
        }
        if (is_array($this->sources) && $this->sources) {
            $request->setParam('sourceCode', array_key_first($this->sources));
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
