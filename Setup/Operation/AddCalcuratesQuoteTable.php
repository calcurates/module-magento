<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Setup\Operation;

use Calcurates\ModuleMagento\Api\Data\QuoteDataInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\QuoteData;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddCalcuratesQuoteTable
{
    /**
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $tableName = QuoteData::TABLE_NAME;
        $table = $setup->getConnection()
            ->newTable($setup->getTable($tableName))
            ->addColumn(
                QuoteDataInterface::ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                QuoteDataInterface::QUOTE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Quote ID'
            )
            ->addColumn(
                QuoteDataInterface::DELIVERY_DATE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Date'
            )
            ->addColumn(
                QuoteDataInterface::DELIVERY_DATE_FEE,
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false],
                'Date Fee'
            )
            ->addColumn(
                QuoteDataInterface::DELIVERY_DATE_TIME_FROM,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Time From'
            )
            ->addColumn(
                QuoteDataInterface::DELIVERY_DATE_TIME_TO,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Time To'
            )
            ->addColumn(
                QuoteDataInterface::DELIVERY_DATE_TIME_FEE,
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false],
                'Date Fee'
            )
            ->addIndex(
                $setup->getIdxName(
                    QuoteData::TABLE_NAME,
                    [QuoteDataInterface::QUOTE_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [QuoteDataInterface::QUOTE_ID],
                AdapterInterface::INDEX_TYPE_UNIQUE
            )
            ->addForeignKey(
                $setup->getFkName(
                    QuoteData::TABLE_NAME,
                    QuoteDataInterface::QUOTE_ID,
                    'quote',
                    'entity_id'
                ),
                QuoteDataInterface::QUOTE_ID,
                $setup->getTable('quote'),
                'entity_id',
                AdapterInterface::FK_ACTION_CASCADE
            )
            ->setComment('Calcurates Quote Additional Data Table');

        $setup->getConnection()->createTable($table);
    }
}
