<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin;

use Magento\Email\Block\Adminhtml\Template\Edit\Form;

class EmailEditFormAddVariables
{
    /**
     * Add Calcurates delivery variables to the list of email variables
     *
     * @param Form $subject
     * @param array $result
     * @return array
     */
    public function afterGetVariables(Form $subject, array $result): array
    {
        $deliveryVariables = [
            [
                'label' => __('Calcurates Variables'),
                'value' => [
                    [
                        'value' => '{{block class="Calcurates\ModuleMagento\Block\Order\DeliveryDateTime" type="date"'
                            . ' area="frontend" order=$order order_id=$order_id'
                            . ' template="Calcurates_ModuleMagento::order/delivery_date_time.phtml"}}',
                        'label' => __('Delivery Date')
                    ],
                    [
                        'value' => '{{block class="Calcurates\ModuleMagento\Block\Order\DeliveryDateTime" type="time"'
                            . ' area="frontend" order=$order order_id=$order_id'
                            . ' template="Calcurates_ModuleMagento::order/delivery_date_time.phtml"}}',
                        'label' => __('Delivery Time Slot')
                    ]
                ]
            ]
        ];
        return array_merge_recursive($result, $deliveryVariables);
    }
}
