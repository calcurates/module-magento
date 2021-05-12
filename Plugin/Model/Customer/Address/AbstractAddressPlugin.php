<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Model\Customer\Address;

use Magento\Customer\Model\Address\AbstractAddress;

/**
 * Fix estimation and other cases, when 'region' is array.
 * Remove when magento fix it.
 */
class AbstractAddressPlugin
{
    /**
     * @param AbstractAddress $subject
     */
    public function beforeGetRegionCode(AbstractAddress $subject)
    {
        $regionId = $subject->getData('region_id');
        $region = $subject->getData('region');

        if (!$regionId && is_array($region)) {
            $subject->setData('region_code', $region['region_code'] ?? null);
        }
    }
}
