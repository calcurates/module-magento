<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Setup\Operation;

use Calcurates\ModuleMagento\Api\Data\ShippingLabelInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\ShippingLabel;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddCarrierCodesToLabelsTable
{
    public function execute(SchemaSetupInterface $setup): void
    {
        $tableName = ShippingLabel::TABLE_NAME;

        $connection = $setup->getConnection();
        $connection->addColumn(
            $setup->getTable($tableName),
            ShippingLabelInterface::CARRIER_CODE,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => '',
                'comment' => 'Calcurates Carrier Code'
            ]
        );

        $connection->addColumn(
            $setup->getTable($tableName),
            ShippingLabelInterface::CARRIER_PROVIDER_CODE,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => '',
                'comment' => 'Calcurates Carrier Provider Code'
            ]
        );
    }
}
