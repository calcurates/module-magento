<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\ResourceModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

class GetSourceCodesPerSkus
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param array $skus
     * @param string $websiteCode
     * @return array
     */
    public function execute(array $skus, string $websiteCode): array
    {
        if (!class_exists(\Magento\Inventory\Model\ResourceModel\SourceItem::class)) {
            return [];
        }
        $tableName = $this->resourceConnection->getTableName(
            \Magento\Inventory\Model\ResourceModel\SourceItem::TABLE_NAME_SOURCE_ITEM
        );
        $connection = $this->resourceConnection->getConnection();

        if (!$connection->isTableExists($tableName)) {
            return [];
        }

        $query = $connection
            ->select()
            ->distinct()
            ->from(['t' => $tableName], ['source_code', 'sku', 'quantity', 'status'])
            ->joinInner(
                [
                    's' => $this->resourceConnection->getTableName('inventory_source')
                ],
                't.source_code = s.source_code AND s.enabled = 1',
                []
            )
            ->joinInner(
                [
                    's_st' => $this->resourceConnection->getTableName('inventory_source_stock_link')
                ],
                't.source_code = s_st.source_code',
                []
            )
            ->joinInner(
                [
                    'st_w' => $this->resourceConnection->getTableName('inventory_stock_sales_channel')
                ],
                's_st.stock_id = st_w.stock_id',
                []
            )
            ->where('sku IN (?)', $skus)
            ->where('st_w.type = ?', 'website')
            ->where('st_w.code = ?', $websiteCode);

        $rows = $connection->fetchAll($query);

        $sourcesBySkus = [];
        $manageStockBySku = $this->getManageStockPerSku($skus);
        foreach ($rows as $row) {
            $manageStock = $manageStockBySku[$row['sku']] ?? 1;

            $quantity = null;
            if ($manageStock) {
                $quantity = $row['status'] == SourceItemInterface::STATUS_IN_STOCK ? floor($row['quantity']) : 0;
            }
            $sourcesBySkus[$row['sku']][] = [
                'source' => $row['source_code'],
                'quantity' => $quantity,
            ];
        }

        return $sourcesBySkus;
    }

    /**
     * @param array $skus
     * @return array
     */
    private function getManageStockPerSku(array $skus): array
    {
        $connection = $this->resourceConnection->getConnection();
        $manageStock = (int)$this->scopeConfig->isSetFlag(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_MANAGE_STOCK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $manageStockExpr = new \Magento\Framework\DB\Sql\Expression(
            'IF(si.use_config_manage_stock = 1, ' . $manageStock . ', si.manage_stock)'
        );
        $productsStocksQuery = $connection->select()
            ->from(
                ['p' => $this->resourceConnection->getTableName('catalog_product_entity')],
                ['sku']
            )->joinInner(
                ['si' => $this->resourceConnection->getTableName('cataloginventory_stock_item')],
                'p.entity_id = si.product_id',
                [
                    'manage_stock' => $manageStockExpr
                ]
            )->where(
                'p.sku IN(?)',
                $skus
            );

        return $connection->fetchPairs($productsStocksQuery);
    }
}
