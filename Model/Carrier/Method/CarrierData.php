<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Carrier\Method;

use Magento\Framework\DataObject;

class CarrierData extends DataObject
{
    const CARRIER_ID = 'carrier_id';
    const SERVICE_IDS_STRING = 'service_ids_string';
    const SERVICE_IDS_ARRAY = 'service_ids_array';

    /**
     * @return string
     */
    public function getCarrierId(): string
    {
        return (string)$this->getData(self::CARRIER_ID);
    }

    /**
     * @return array
     */
    public function getServiceIds(): array
    {
        return $this->getData(self::SERVICE_IDS_ARRAY);
    }

    /**
     * @return string
     */
    public function getServiceIdsString(): string
    {
        return (string)$this->getData(self::SERVICE_IDS_STRING);
    }
}
