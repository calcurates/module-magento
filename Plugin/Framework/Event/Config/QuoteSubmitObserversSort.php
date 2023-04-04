<?php

namespace Calcurates\ModuleMagento\Plugin\Framework\Event\Config;

use Magento\Framework\Event\Config;

class QuoteSubmitObserversSort
{
    /**
     * Rearrange "sales_model_service_quote_submit_success" observers list to run Calcurates before sending order emails
     *
     * @param Config $subject
     * @param array $result
     * @param string $eventName
     * @return array
     */
    public function afterGetObservers(Config $subject, array $result, string $eventName): array
    {
        if ($eventName === 'sales_model_service_quote_submit_success'
            && !empty($result['calcurates_convert_quote_to_order'])
        ) {
            return array_merge(
                [ 'calcurates_convert_quote_to_order' => $result['calcurates_convert_quote_to_order']],
                $result
            );
        }
        return $result;
    }
}
