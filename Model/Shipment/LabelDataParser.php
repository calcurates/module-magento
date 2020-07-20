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
use Magento\Framework\Serialize\SerializerInterface;

class LabelDataParser
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var CurrencyFactory
     */
    private $currencyFactory;

    /**
     * ShipmentLabelDataHelper constructor.
     * @param SerializerInterface $serializer
     * @param PriceCurrencyInterface $priceCurrency
     * @param CurrencyFactory $currencyFactory
     */
    public function __construct(
        SerializerInterface $serializer,
        PriceCurrencyInterface $priceCurrency,
        CurrencyFactory $currencyFactory
    ) {
        $this->serializer = $serializer;
        $this->priceCurrency = $priceCurrency;
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * @param string $shippingLabelData
     * @return array
     */
    public function parse(string $shippingLabelData): array
    {
        if (!$shippingLabelData) {
            return [];
        }

        $shippingLabelData = $this->serializer->unserialize($shippingLabelData);

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
