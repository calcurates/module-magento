<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Setup\Operation;

use Calcurates\ModuleMagento\Api\Data\OrderDataInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\OrderData;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddCalcuratesOrderTable
{
    /**
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $tableName = OrderData::TABLE_NAME;
        $table = $setup->getConnection()
            ->newTable($setup->getTable($tableName))
            ->addColumn(
                OrderDataInterface::ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                OrderDataInterface::ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Order ID'
            )
            ->addColumn(
                OrderDataInterface::DELIVERY_DATE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Date'
            )
            ->addColumn(
                OrderDataInterface::DELIVERY_DATE_TIME_FROM,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Time From'
            )
            ->addColumn(
                OrderDataInterface::DELIVERY_DATE_TIME_TO,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Time To'
            )
            ->addColumn(
                OrderDataInterface::BASE_DD_FEE_AMOUNT,
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false],
                'Whole delivery date fee amount in base currency'
            )
            ->addColumn(
                OrderDataInterface::DD_FEE_AMOUNT,
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false],
                'Whole delivery date fee amount in order currency'
            )
            ->addColumn(
                OrderDataInterface::DELIVERY_DATES,
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Delivery Dates serialized for current method'
            )
            ->addIndex(
                $setup->getIdxName(
                    OrderData::TABLE_NAME,
                    [OrderDataInterface::ORDER_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [OrderDataInterface::ORDER_ID],
                AdapterInterface::INDEX_TYPE_UNIQUE
            )
            ->addForeignKey(
                $setup->getFkName(
                    OrderData::TABLE_NAME,
                    OrderDataInterface::ORDER_ID,
                    'sales_order',
                    'entity_id'
                ),
                OrderDataInterface::ORDER_ID,
                $setup->getTable('sales_order'),
                'entity_id',
                AdapterInterface::FK_ACTION_CASCADE
            )
            ->setComment('Calcurates Order Additional Data Table');

        $setup->getConnection()->createTable($table);
    }
}
