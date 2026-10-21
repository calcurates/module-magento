<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2026 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Cart\Shipping\Rate;

use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Magento\Framework\DataObject;

/**
 * Builds the rate details that should be stored with the quote and the order.
 * The shipping cost itself is omitted: Magento already stores it as the shipping amount.
 */
class ShippingRateSnapshot
{
    /**
     * @param DataObject $rate
     * @return array
     */
    public function fromRate(DataObject $rate): array
    {
        $data = [];
        $currency = $rate->getData(RatesResponseProcessor::CALCURATES_CURRENCY);
        if ($currency) {
            $data['currency'] = (string)$currency;
        }

        $this->copyAmount($data, 'tax_amount', $rate, RatesResponseProcessor::CALCURATES_TAX_AMOUNT);
        $this->copyAmount($data, 'insurance_amount', $rate, RatesResponseProcessor::CALCURATES_INSURANCE_AMOUNT);
        $this->copyAmount($data, 'base_rate', $rate, RatesResponseProcessor::CALCURATES_BASE_RATE);
        $this->copyAmount($data, 'surcharge', $rate, RatesResponseProcessor::CALCURATES_SURCHARGE);
        $this->copyAmount($data, 'discount', $rate, RatesResponseProcessor::CALCURATES_DISCOUNT);
        $this->copyRules($data, 'surcharge_rules', $rate->getData(RatesResponseProcessor::CALCURATES_SURCHARGE_RULES));
        $this->copyRules($data, 'discount_rules', $rate->getData(RatesResponseProcessor::CALCURATES_DISCOUNT_RULES));
        $this->copyDeliveryDates($data, $rate->getData(RatesResponseProcessor::CALCURATES_DELIVERY_DATES));

        $packages = $rate->getData(RatesResponseProcessor::CALCURATES_PACKAGES);
        if (is_array($packages) && $packages) {
            $groupedPackages = $this->groupPackages($packages);
            if ($groupedPackages) {
                $data['packages'] = $groupedPackages;
            }
        }

        return $data;
    }

    /**
     * @param array $packages
     * @return array
     */
    public function groupPackages(array $packages): array
    {
        $grouped = [];
        foreach ($packages as $package) {
            if (!is_array($package)) {
                continue;
            }
            $name = (string)($package['name'] ?? ($package['code'] ?? ''));
            if ($name === '') {
                continue;
            }
            $key = (string)($package['customPackageId'] ?? ($package['code'] ?? $name));
            if (isset($grouped[$key])) {
                $grouped[$key]['qty']++;
                continue;
            }
            $grouped[$key] = [
                'name' => $name,
                'qty' => 1,
            ];
        }

        return array_values($grouped);
    }

    /**
     * @param array $data
     * @param string $key
     * @param DataObject $rate
     * @param string $sourceKey
     * @return void
     */
    private function copyAmount(array &$data, string $key, DataObject $rate, string $sourceKey): void
    {
        if (!$rate->hasData($sourceKey)) {
            return;
        }
        $amount = $rate->getData($sourceKey);
        if ($amount === null || $amount === '') {
            return;
        }
        $data[$key] = $amount;
    }

    /**
     * @param array $data
     * @param string $key
     * @param mixed $rules
     * @return void
     */
    private function copyRules(array &$data, string $key, $rules): void
    {
        if (!is_array($rules) || !$rules) {
            return;
        }
        $normalized = [];
        foreach ($rules as $ruleId => $amount) {
            if (is_array($amount)) {
                $amount = $amount['amount'] ?? $amount['cost'] ?? null;
            }
            if ($amount === null || $amount === '') {
                continue;
            }
            $normalized[(string)$ruleId] = $amount;
        }
        if ($normalized) {
            $data[$key] = $normalized;
        }
    }

    /**
     * @param array $data
     * @param mixed $deliveryDates
     * @return void
     */
    private function copyDeliveryDates(array &$data, $deliveryDates): void
    {
        if (!is_array($deliveryDates)) {
            return;
        }
        if (!empty($deliveryDates['from'])) {
            $data['delivery_from'] = $deliveryDates['from'];
        }
        if (!empty($deliveryDates['to'])) {
            $data['delivery_to'] = $deliveryDates['to'];
        }
        if (isset($deliveryDates['daysInTransitFrom']) && $deliveryDates['daysInTransitFrom'] !== '') {
            $data['min_transit_days'] = $deliveryDates['daysInTransitFrom'];
        }
        if (isset($deliveryDates['daysInTransitTo']) && $deliveryDates['daysInTransitTo'] !== '') {
            $data['max_transit_days'] = $deliveryDates['daysInTransitTo'];
        }
    }
}
