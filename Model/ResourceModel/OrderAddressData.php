<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Calcurates\ModuleMagento\Api\SalesData\OrderData\OrderAddressExtensionAttributesInterface;

class OrderAddressData extends AbstractDb
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            OrderAddressExtensionAttributesInterface::ORDER_ADDRESS_EXTENSION_TABLE,
            'id'
        );
    }
}
