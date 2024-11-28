<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\Model\Rule\Condition;

use Calcurates\ModuleMagento\Model\Carrier;
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
use Magento\SalesRule\Model\Rule\Condition\Address;

class AddressPlugin
{
    /**
     * @var ShippingMethodManager
     */
    private $shippingMethodManager;

    /**
     * AddressPlugin constructor.
     * @param ShippingMethodManager $shippingMethodManager
     */
    public function __construct(ShippingMethodManager $shippingMethodManager)
    {
        $this->shippingMethodManager = $shippingMethodManager;
    }

    /**
     * Condition value seems as calcurates_carrier_{carrierID}_{serviceID} but real attrubute value is
     * calcurates_carrier_{carrierID}_{serviceIDsArray}_{someIncrementNumber}
     * This plugin fix validation for these values
     *
     * @param Address $subject
     * @param bool $result
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function afterValidate(Address $subject, $result, \Magento\Framework\Model\AbstractModel $model)
    {
        $prefix = Carrier::CODE . '_' . ShippingMethodManager::CARRIER . '_';
        $attributeValue = $model->getData($subject->getAttribute());
        if ('shipping_method' != $subject->getAttribute()
            || strpos($subject->getValue(), $prefix) !== 0
            || strpos($attributeValue, $prefix) !== 0
            || strpos($attributeValue, ',') === false
        ) {
            return $result;
        }

        $expectedCarrierData = $this->shippingMethodManager->getCarrierData($subject->getValue());
        $carrierData = $this->shippingMethodManager->getCarrierData($attributeValue);

        if (in_array($expectedCarrierData->getServiceIdsString(), $carrierData->getServiceIds())) {
            return $subject->validateAttribute($subject->getValue());
        }

        return $result;
    }
}
