<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Setup\Operation;

use Calcurates\ModuleMagento\Api\Data\ManifestInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\Manifest;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddCalcuratesManifestTable
{
    public function execute(SchemaSetupInterface $setup)
    {
        $tableName = Manifest::TABLE_NAME;
        $table = $setup->getConnection()
            ->newTable($setup->getTable($tableName))
            ->addColumn(
                ManifestInterface::MANIFEST_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                ManifestInterface::PDF_CONTENT,
                Table::TYPE_BLOB,
                16777216, // mediumblob
                ['nullable' => true],
                'Calcurates PDF Manifest Content'
            )
            ->addColumn(
                ManifestInterface::MANIFEST_DATA,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Calcurates Manifest Data Json from API'
            )
            ->addColumn(
                ManifestInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->setComment('Calcurates Manifests');

        $setup->getConnection()->createTable($table);
    }
}
