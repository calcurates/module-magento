<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Carrier\Method;

use Magento\Framework\DataObject;

class InStorePickupData extends DataObject
{
    public const CARRIER_ID = 'carrier_id';
    public const SERVICE_ID = 'service_id';

    /**
     * @return int
     */
    public function getCarrierId(): int
    {
        return (int) $this->getData(self::CARRIER_ID);
    }

    /**
     * @return int
     */
    public function getServiceId(): int
    {
        return (int) $this->getData(self::SERVICE_ID);
    }
}
