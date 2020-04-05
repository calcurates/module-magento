<?php

namespace Calcurates\ModuleMagento\Client\Request;

use Calcurates\ModuleMagento\Model\Source\ShipmentServiceRetriever;
use Calcurates\ModuleMagento\Model\Source\ShipmentSourceCodeRetriever;

class ShippingLabelRequestBuilder
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var ShipmentSourceCodeRetriever
     */
    private $shipmentSourceCodeRetriever;

    /**
     * @var ShipmentServiceRetriever
     */
    private $shipmentServiceRetriever;

    /**
     * ShippingLabelRequestBuilder constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param ShipmentSourceCodeRetriever $shipmentSourceCodeRetriever
     * @param ShipmentServiceRetriever $shipmentServiceRetriever
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        ShipmentSourceCodeRetriever $shipmentSourceCodeRetriever,
        ShipmentServiceRetriever $shipmentServiceRetriever
    ) {
        $this->request = $request;
        $this->shipmentSourceCodeRetriever = $shipmentSourceCodeRetriever;
        $this->shipmentServiceRetriever = $shipmentServiceRetriever;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     * @param bool $testLabel
     * @return array
     */
    public function build(\Magento\Framework\DataObject $request, $testLabel = false)
    {
        /** @var \Magento\Shipping\Model\Shipment\Request $request */
        $shippingService = $this->request->getParam('calcuratesShippingServiceId');
        $sourceCode = $this->shipmentSourceCodeRetriever->retrieve($request->getOrderShipment());
        if (!$shippingService) {
            $shippingService = $this->shipmentServiceRetriever->retrieve(
                $request->getOrderShipment()->getOrder(),
                $sourceCode
            );
        }

        $apiRequestBody = [
            'service' => $shippingService,
            'source' => $sourceCode,
            'shipTo' => [
                'name' => $request->getRecipientContactPersonName(),
                'phone' => $request->getRecipientContactPhoneNumber(),
                'companyName' => $request->getRecipientContactCompanyName(),
                'addressLine1' => $request->getRecipientAddressStreet1(),
                'addressLine2' => $request->getRecipientAddressStreet2(),
                'city' => $request->getRecipientAddressCity(),
                'region' => $request->getRecipientAddressStateOrProvinceCode(),
                'postalCode' => $request->getRecipientAddressPostalCode(),
                'country' => $request->getRecipientAddressCountryCode(),
                'addressResidentialIndicator' => 'unknown',
            ],
            'packages' => [],
            'testLabel' => $testLabel,
            'validateAddress' => 'no_validation',
        ];

        foreach ($request->getPackages() as $package) {
            $rawPackage = [
                'weight' => [
                    'value' => $package['params']['weight'],
                    'unit' => $this->getWeightUnits($package['params']['weight_units']),
                ],
                'dimensions' => [
                    'length' => $package['params']['length'],
                    'width' => $package['params']['width'],
                    'height' => $package['params']['height'],
                    'unit' => $this->getDimensionUnits($package['params']['dimension_units']),
                ],
            ];
            $apiRequestBody['packages'][] = $rawPackage;
        }

        return $apiRequestBody;
    }

    /**
     * @param string $weightUnits
     * @return string
     */
    private function getWeightUnits($weightUnits)
    {
        switch ($weightUnits) {
            case \Zend_Measure_Weight::POUND:
                $weightUnits = 'pound';
                break;
            case \Zend_Measure_Weight::KILOGRAM:
                $weightUnits = 'kilogram';
                break;
            case \Zend_Measure_Weight::OUNCE:
                $weightUnits = 'ounce';
                break;
            case \Zend_Measure_Weight::GRAM:
                $weightUnits = 'gram';
                break;
            default:
                throw new \InvalidArgumentException('Invalid weight units');
        }

        return $weightUnits;
    }

    /**
     * @param string $dimensionUnits
     * @return string
     */
    private function getDimensionUnits($dimensionUnits)
    {
        switch ($dimensionUnits) {
            case \Zend_Measure_Length::INCH:
                $dimensionUnits = 'inch';
                break;
            case \Zend_Measure_Length::CENTIMETER:
                $dimensionUnits = 'centimeter';
                break;
            default:
                throw new \InvalidArgumentException('Invalid dimension units');
        }

        return $dimensionUnits;
    }
}
