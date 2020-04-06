<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */
namespace Calcurates\ModuleMagento\Client\Request;

use Calcurates\ModuleMagento\Api\Shipping\ShippingDataResolverInterface;

class ShippingLabelRequestBuilder
{
    /**
     * @var ShippingDataResolverInterface
     */
    private $shippingDataResolver;

    /**
     * ShippingLabelRequestBuilder constructor.
     * @param ShippingDataResolverInterface $shippingDataResolver
     */
    public function __construct(
        ShippingDataResolverInterface $shippingDataResolver
    ) {
        $this->shippingDataResolver = $shippingDataResolver;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     * @param bool $testLabel
     * @return array
     */
    public function build(\Magento\Framework\DataObject $request, $testLabel = false)
    {
        /** @var \Magento\Shipping\Model\Shipment\Request $request */
        $shippingData = $this->shippingDataResolver->getShippingData($request->getOrderShipment());

        $apiRequestBody = [
            'service' => $shippingData->getShippingServiceId(),
            'source' => $shippingData->getSourceCode(),
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
