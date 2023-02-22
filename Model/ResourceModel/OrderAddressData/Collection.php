<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\ResourceModel\OrderAddressData;

use Calcurates\ModuleMagento\Model\ResourceModel\OrderAddressData as Resource;
use Calcurates\ModuleMagento\Model\SalesData\OrderData\OrderAddress;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Init models
     */
    protected function _construct(): void
    {
        $this->_init(OrderAddress::class, Resource::class);
    }
}
