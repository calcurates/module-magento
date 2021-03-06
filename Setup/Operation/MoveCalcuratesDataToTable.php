<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Setup\Operation;

use Calcurates\ModuleMagento\Api\Data\OrderDataInterface;
use Calcurates\ModuleMagento\Api\Data\QuoteDataInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\OrderData;
use Calcurates\ModuleMagento\Model\ResourceModel\QuoteData;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class MoveCalcuratesDataToTable
{
    public function execute(ModuleDataSetupInterface $setup): void
    {
        $connection = $setup->getConnection();

        $quoteTable = $setup->getTable('quote');
        $quoteDataTable = $setup->getTable(QuoteData::TABLE_NAME);
        $orderTable = $setup->getTable('sales_order');
        $orderDataTable = $setup->getTable(OrderData::TABLE_NAME);
        $deliveryDatesFieldName = 'calcurates_delivery_dates_data';

        if ($connection->tableColumnExists($quoteTable, $deliveryDatesFieldName)) {
            $quoteSelect = $connection
                ->select()
                ->from(
                    $quoteTable,
                    [
                        QuoteDataInterface::DELIVERY_DATES => $deliveryDatesFieldName,
                        QuoteDataInterface::QUOTE_ID => 'entity_id'
                    ]
                )
                ->where($deliveryDatesFieldName . ' IS NOT NULL')
                ->where($deliveryDatesFieldName . " <> ''");

            $connection->insertOnDuplicate(
                $quoteDataTable,
                $connection->fetchAll($quoteSelect),
                [QuoteDataInterface::DELIVERY_DATES]
            );

            $connection->dropColumn($quoteTable, $deliveryDatesFieldName);
        }

        if ($connection->tableColumnExists($orderTable, $deliveryDatesFieldName)) {
            $orderSelect = $connection
                ->select()
                ->from(
                    $orderTable,
                    [
                        OrderDataInterface::DELIVERY_DATES => $deliveryDatesFieldName,
                        OrderDataInterface::ORDER_ID => 'entity_id'
                    ]
                )
                ->where($deliveryDatesFieldName . ' IS NOT NULL')
                ->where($deliveryDatesFieldName . " <> ''");

            $connection->insertOnDuplicate(
                $orderDataTable,
                $connection->fetchAll($orderSelect),
                [OrderDataInterface::DELIVERY_DATES]
            );
            $connection->dropColumn($orderTable, $deliveryDatesFieldName);
        }
    }
}
