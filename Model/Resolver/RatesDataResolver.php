<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Resolver;

use Calcurates\ModuleMagento\Api\Data\RateDataInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class RatesDataResolver implements ResolverInterface
{
    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return \Magento\Framework\GraphQl\Query\Resolver\Value|mixed|string[]
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $fields = [
            RateDataInterface::TOOLTIP_MESSAGE,
            RateDataInterface::MAP_LINK,
            RateDataInterface::IMAGE_URL,
        ];

        $data = [];
        foreach ($fields as $fieldKey) {
            $data[$fieldKey] = $value['calcurates_data'][$fieldKey] ?? null;
        }

        $data['delivery_dates_list'] = [];
        foreach ($value['calcurates_data']['delivery_dates_list'] ?? [] as $deliveryDate) {
            $deliveryDateArray = [
                'id' => $deliveryDate['id'],
                'date' => $deliveryDate['date'],
                'date_formatted' => $deliveryDate['date_formatted'],
                'fee_amount' => $deliveryDate['fee_amount']
            ];

            $deliveryDateArray['time_intervals'] = [];
            foreach ($deliveryDate['time_intervals'] as $timeInterval) {
                $deliveryDateArray['time_intervals'][] = [
                    'id' => $timeInterval['id'],
                    'fee_amount' => $timeInterval['fee_amount'],
                    'from' => $timeInterval['from'],
                    'to' => $timeInterval['to'],
                    'interval_formatted' => $timeInterval['interval_formatted']
                ];
            }

            $data['delivery_dates_list'][] = $deliveryDateArray;
        }

        $data['delivery_dates_meta']['is_delivery_date_required'] =
            $value['calcurates_data']['metadata']['delivery_dates_metadata']
            ['time_slot_date_required'] ?? null;

        $data['delivery_dates_meta']['is_delivery_time_required'] =
            $value['calcurates_data']['metadata']['delivery_dates_metadata']
            ['time_slot_time_required'] ?? null;

        return $data;
    }
}
