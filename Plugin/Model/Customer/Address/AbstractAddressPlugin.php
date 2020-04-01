<?php

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
