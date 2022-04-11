<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Block\Adminhtml\Order\View;

use Magento\Sales\Block\Adminhtml\Order\View\Info;
use Magento\Sales\Model\Order\Address;

class InfoPlugin
{
    /**
     * Ship-to address validation status
     * @param Info $subject
     * @param string $result
     * @param Address $address
     * @return string
     */
    public function afterGetFormattedAddress(Info $subject, string $result, Address $address): string
    {
        if ($address->getAddressType() == 'shipping'
            && $address->getExtensionAttributes()
            && $address->getExtensionAttributes()->getResidentialDelivery()
            && $address->getExtensionAttributes()->getResidentialDelivery()->getResidentialDelivery() !== null
        ) {
            $label = $address->getExtensionAttributes()->getResidentialDelivery()->getResidentialDelivery() == 1
                ? __('Residential')
                : __('Commercial');
            $result .= '<br><i class="icon success"></i><em>'. $label .'</em>';
        }
        return $result;
    }
}
