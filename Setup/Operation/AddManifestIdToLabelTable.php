<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Setup\Operation;

use Calcurates\ModuleMagento\Api\Data\ShippingLabelInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\ShippingLabel;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddManifestIdToLabelTable
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup): void
    {
        $tableName = ShippingLabel::TABLE_NAME;
        $connection = $setup->getConnection();
        $connection->addColumn(
            $setup->getTable($tableName),
            ShippingLabelInterface::MANIFEST_ID,
            [
                'type' => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => true,
                'comment' => 'Calcurates Manifest ID'
            ]
        );
    }
}
