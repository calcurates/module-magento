<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Setup\Operation;

use Calcurates\ModuleMagento\Api\Data\ShippingLabelInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\ShippingLabel;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddCalcuratesLabelTable
{
    public function execute(SchemaSetupInterface $setup)
    {
        $tableName = ShippingLabel::TABLE_NAME;
        $table = $setup->getConnection()
            ->newTable($setup->getTable($tableName))
            ->addColumn(
                ShippingLabelInterface::ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                ShippingLabelInterface::SHIPMENT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Shipment Id'
            )
            ->addColumn(
                ShippingLabelInterface::SHIPPING_CARRIER_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Calcurates Shipping Carrier Id'
            )
            ->addColumn(
                ShippingLabelInterface::SHIPPING_SERVICE_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Calcurates Shipping Service Id'
            )
            ->addColumn(
                ShippingLabelInterface::SHIPPING_CARRIER_LABEL,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Calcurates Shipping Carrier Label'
            )
            ->addColumn(
                ShippingLabelInterface::SHIPPING_SERVICE_LABEL,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Calcurates Shipping Service Label'
            )
            ->addColumn(
                ShippingLabelInterface::TRACKING_NUMBER,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Calcurates Tracking Number'
            )
            ->addColumn(
                ShippingLabelInterface::LABEL_CONTENT,
                Table::TYPE_BLOB,
                16777216, // mediumblob
                ['nullable' => true],
                'Calcurates PDF Label Content'
            )
            ->addColumn(
                ShippingLabelInterface::LABEL_DATA,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Calcurates Label Data Json from API'
            )
            ->addColumn(
                ShippingLabelInterface::PACKAGES,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Packages JSON from magento'
            )
            ->addColumn(
                ShippingLabelInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->setComment('Calcurates Multi-labels table');

        $setup->getConnection()->createTable($table);
    }
}
