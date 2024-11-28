<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2022 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Setup\Patch\Data;

use Calcurates\ModuleMagento\Api\Data\OrderDataInterface;
use Calcurates\ModuleMagento\Api\Data\QuoteDataInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\OrderData;
use Calcurates\ModuleMagento\Model\ResourceModel\QuoteData;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MoveCalcuratesDataToTable implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup;
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

            $quotes = $connection->fetchAll($quoteSelect);
            if (!empty($quotes)) {
                $connection->insertOnDuplicate(
                    $quoteDataTable,
                    $quotes,
                    [QuoteDataInterface::DELIVERY_DATES]
                );
            }
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

            $orders = $connection->fetchAll($orderSelect);
            if (!empty($orders)) {
                $connection->insertOnDuplicate(
                    $orderDataTable,
                    $orders,
                    [OrderDataInterface::DELIVERY_DATES]
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
