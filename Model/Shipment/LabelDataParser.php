<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Shipment;

use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class LabelDataParser
{
    /**
     * @var CurrencyFactory
     */
    private $currencyFactory;

    public function __construct(
        CurrencyFactory $currencyFactory
    ) {
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * @param array $shippingLabelData
     * @return array
     */
    public function parse(array $shippingLabelData): array
    {
        if (!$shippingLabelData) {
            return [];
        }

        $keyLabel = [
            'trackingNumber' => __('Tracking Number'),
            'shipDate' => __('Ship Date'),
            'trackable' => __('Trackable'),
            'shipmentCost' => __('Shipment Cost')
        ];

        foreach ($keyLabel as $requiredKey => $label) {
            if (!isset($shippingLabelData[$requiredKey])) {
                $shippingLabelData[$requiredKey] = '';
            }
        }

        if (isset($shippingLabelData['shipmentCost']['value'])
            && isset($shippingLabelData['shipmentCost']['currency'])) {
            $currency = $this->currencyFactory->create()
                ->load($shippingLabelData['shipmentCost']['currency']);
            $shippingLabelData['shipmentCost'] = $currency->formatPrecision(
                $shippingLabelData['shipmentCost']['value'],
                PriceCurrencyInterface::DEFAULT_PRECISION,
                [],
                false
            );
        }

        $shippingLabelData['trackable'] = $shippingLabelData['trackable'] ? __('Yes') : __('No');

        $dataWithLabels = [];
        foreach ($keyLabel as $key => $label) {
            $dataWithLabels[] = [
                'label' => $label,
                'value' => $shippingLabelData[$key]
            ];
        }

        return $dataWithLabels;
    }
}
