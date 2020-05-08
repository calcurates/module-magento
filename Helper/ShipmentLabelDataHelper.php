<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Helper;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\Order\Shipment;

class ShipmentLabelDataHelper extends AbstractHelper
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
     * @param Context $context
     * @param SerializerInterface $serializer
     * @param PriceCurrencyInterface $priceCurrency
     * @param CurrencyFactory $currencyFactory
     */
    public function __construct(
        Context $context,
        SerializerInterface $serializer,
        PriceCurrencyInterface $priceCurrency,
        CurrencyFactory $currencyFactory
    ) {
        parent::__construct($context);
        $this->serializer = $serializer;
        $this->priceCurrency = $priceCurrency;
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * @param Shipment $orderShipment
     * @return array|null
     */
    public function getLabelData(Shipment $orderShipment)
    {
        $shippingLabelData = $orderShipment->getData(CustomSalesAttributesInterface::LABEL_DATA);

        if (!$shippingLabelData) {
            return null;
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
