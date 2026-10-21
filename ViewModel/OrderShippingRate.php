<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2026 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\ViewModel;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Calcurates\ModuleMagento\Api\Data\OrderDataInterface;
use Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\ShippingRateSnapshot;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;

class OrderShippingRate implements ArgumentInterface
{
    /**
     * @var CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ShippingRateSnapshot
     */
    private $shippingRateSnapshot;

    /**
     * @param CurrencyInterface $localeCurrency
     * @param SerializerInterface $serializer
     * @param ShippingRateSnapshot $shippingRateSnapshot
     */
    public function __construct(
        CurrencyInterface $localeCurrency,
        SerializerInterface $serializer,
        ShippingRateSnapshot $shippingRateSnapshot
    ) {
        $this->localeCurrency = $localeCurrency;
        $this->serializer = $serializer;
        $this->shippingRateSnapshot = $shippingRateSnapshot;
    }

    /**
     * Rate details for the admin order view.
     * Dates already stored on the order are used when the rate snapshot does not contain them.
     *
     * @param OrderDataInterface $orderData
     * @param OrderInterface $order
     * @return array
     */
    public function getDetails(OrderDataInterface $orderData, OrderInterface $order): array
    {
        $rate = $orderData->getShippingRate();
        $dates = $orderData->getDeliveryDates();
        $shipments = is_array($rate['shipments'] ?? null) ? $rate['shipments'] : [];
        $packages = is_array($rate['packages'] ?? null) ? $rate['packages'] : [];
        if (!$packages && !$shipments) {
            $packages = $this->packagesFromOrder($order);
        }
        foreach ($shipments as &$shipment) {
            if (is_array($shipment['rate'] ?? null)) {
                $shipment['rate']['insurance_amount'] = $this->shippingRateSnapshot->normalizeAmount(
                    $shipment['rate']['insurance_amount'] ?? null
                );
            }
        }
        unset($shipment);

        return [
            'delivery_from' => $rate['delivery_from'] ?? ($dates['from'] ?? null),
            'delivery_to' => $rate['delivery_to'] ?? ($dates['to'] ?? null),
            'min_transit_days' => $rate['min_transit_days'] ?? ($dates['daysInTransitFrom'] ?? null),
            'max_transit_days' => $rate['max_transit_days'] ?? ($dates['daysInTransitTo'] ?? null),
            'currency' => $rate['currency'] ?? null,
            'tax_amount' => $rate['tax_amount'] ?? null,
            'insurance_amount' => $this->shippingRateSnapshot->normalizeAmount($rate['insurance_amount'] ?? null),
            'base_rate' => $rate['base_rate'] ?? null,
            'surcharge' => $rate['surcharge'] ?? null,
            'surcharge_rules' => is_array($rate['surcharge_rules'] ?? null) ? $rate['surcharge_rules'] : [],
            'discount' => $rate['discount'] ?? null,
            'discount_rules' => is_array($rate['discount_rules'] ?? null) ? $rate['discount_rules'] : [],
            'packages' => $packages,
            'shipments' => $shipments,
        ];
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function isPresent($value): bool
    {
        return $value !== null && $value !== '';
    }

    /**
     * @param array $rate
     * @return bool
     */
    public function hasDeliveryFields(array $rate): bool
    {
        return $this->isPresent($rate['delivery_from'] ?? null)
            || $this->isPresent($rate['delivery_to'] ?? null)
            || $this->isPresent($rate['min_transit_days'] ?? null)
            || $this->isPresent($rate['max_transit_days'] ?? null);
    }

    /**
     * @param array $rate
     * @return bool
     */
    public function hasPriceFields(array $rate): bool
    {
        return $this->isPresent($rate['tax_amount'] ?? null)
            || $this->isPresent($rate['insurance_amount'] ?? null)
            || $this->isPresent($rate['base_rate'] ?? null)
            || $this->isPresent($rate['surcharge'] ?? null)
            || $this->isPresent($rate['discount'] ?? null)
            || !empty($rate['surcharge_rules'])
            || !empty($rate['discount_rules'])
            || !empty($rate['packages']);
    }

    /**
     * Format an amount in its own currency, without converting it to the store currency.
     *
     * @param float|int|string $amount
     * @param string|null $currency
     * @return string
     */
    public function formatAmount($amount, ?string $currency): string
    {
        if (!$currency) {
            return number_format((float)$amount, 2, '.', '');
        }

        try {
            return $this->localeCurrency->getCurrency($currency)->toCurrency((float)$amount);
        } catch (\Exception $exception) {
            return number_format((float)$amount, 2, '.', '') . ' ' . $currency;
        }
    }

    /**
     * @param OrderInterface $order
     * @return array
     */
    private function packagesFromOrder(OrderInterface $order): array
    {
        $raw = $order->getData(CustomSalesAttributesInterface::CARRIER_PACKAGES);
        if (!$raw || !is_string($raw)) {
            return [];
        }

        try {
            $decoded = $this->serializer->unserialize($raw);
        } catch (\InvalidArgumentException $exception) {
            return [];
        }
        if (!is_array($decoded)) {
            return [];
        }

        return $this->shippingRateSnapshot->groupPackages($this->flattenPackages($decoded));
    }

    /**
     * @param array $node
     * @return array
     */
    private function flattenPackages(array $node): array
    {
        if (array_key_exists('name', $node) || array_key_exists('code', $node)) {
            return [$node];
        }

        $packages = [];
        foreach ($node as $value) {
            if (is_array($value)) {
                $packages = array_merge($packages, $this->flattenPackages($value));
            }
        }

        return $packages;
    }
}
