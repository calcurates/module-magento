<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\ResourceModel;

use Calcurates\ModuleMagento\Api\Data\ShippingLabelInterface;

class ShippingLabel extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const TABLE_NAME = 'calcurates_shipping_label';

    /**
     * Fields that should be serialized before persistence
     *
     * @var array
     */
    protected $_serializableFields = [ShippingLabelInterface::PACKAGES => [[], []]];

    /**
     * Init table and id field
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ShippingLabelInterface::ID);
    }
}
