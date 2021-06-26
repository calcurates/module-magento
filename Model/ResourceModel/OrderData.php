<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\ResourceModel;

use Calcurates\ModuleMagento\Api\Data\OrderDataInterface;

class OrderData extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const TABLE_NAME = 'calcurates_order_data';

    protected $_serializableFields = [
        OrderDataInterface::DELIVERY_DATES => [
            '[]',
            []
        ]
    ];

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, OrderDataInterface::ID);
    }
}
