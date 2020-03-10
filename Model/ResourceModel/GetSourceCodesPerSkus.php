<?php

namespace Calcurates\ModuleMagento\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

class GetSourceCodesPerSkus
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param array $skus
     * @return array
     */
    public function execute(array $skus): array
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

        $qry = $connection
            ->select()
            ->distinct()
            ->from($tableName, ['source_code', 'sku'])
            ->where('sku IN (?)', $skus);

        $rows = $connection->fetchAll($qry);

        $sourcesBySkus = [];
        foreach ($rows as $row) {
            $sourcesBySkus[$row['sku']][] = $row['source_code'];
        }

        return $sourcesBySkus;
    }
}
