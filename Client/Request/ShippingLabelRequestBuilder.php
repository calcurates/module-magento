<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client\Request;

use Calcurates\ModuleMagento\Model\Source\ShipmentSourceCodeRetriever;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Directory\Model\RegionFactory;

class ShippingLabelRequestBuilder
{
    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductAttributesService
     */
    private $productAttributesService;

    /**
     * @var ShipmentSourceCodeRetriever
     */
    private $shipmentSourceCodeRetriever;

    public function __construct(
        RegionFactory $regionFactory,
        ProductRepositoryInterface $productRepository,
        ProductAttributesService $productAttributesService,
        ShipmentSourceCodeRetriever $shipmentSourceCodeRetriever
    ) {
        $this->regionFactory = $regionFactory;
        $this->productRepository = $productRepository;
        $this->productAttributesService = $productAttributesService;
        $this->shipmentSourceCodeRetriever = $shipmentSourceCodeRetriever;
    }

    /**
     * @param \Magento\Framework\DataObject|\Magento\Shipping\Model\Shipment\Request $request
     * @param bool $testLabel
     * @return array
     */
    public function build(\Magento\Framework\DataObject $request, $testLabel = false)
    {
        $shippingAddress = $request->getOrderShipment()->getOrder()->getShippingAddress();
        $regionModel = $this->getRegionModel($shippingAddress);

        $sourceCode = $this->shipmentSourceCodeRetriever->retrieve($request->getOrderShipment());

        $apiRequestBody = [
            'source' => $sourceCode,
            'serviceCode' => $request->getData('calcurates_service_code'),
            'carrierCode' => $request->getData('calcurates_carrier_code'),
            'providerCode' => $request->getData('calcurates_provider_code'),
            'shipTo' => [
                'contactName' => $request->getRecipientContactPersonName(),
                'contactPhone' => $request->getRecipientContactPhoneNumber(),
                'companyName' => $request->getRecipientContactCompanyName(),
                'addressLine1' => $request->getRecipientAddressStreet1(),
                'addressLine2' => $request->getRecipientAddressStreet2(),
                'city' => $request->getRecipientAddressCity(),
                'regionCode' => $regionModel ? $regionModel->getCode() : null,
                'regionName' => $regionModel ? $regionModel->getName() : $shippingAddress->getRegion(),
                'postalCode' => $request->getRecipientAddressPostalCode(),
                'country' => $request->getRecipientAddressCountryCode(),
                'addressResidentialIndicator' => 'unknown',
            ],
            'packages' => [],
            'testLabel' => $testLabel,
            'validateAddress' => 'no_validation',
            'products' => [],
        ];

        /** @var \Magento\Sales\Api\Data\ShipmentItemInterface $item */
        foreach ($request->getOrderShipment()->getAllItems() as $item) {
            $product = $this->productRepository->getById($item->getProductId());
            $apiRequestBody['products'][] = [
                'priceWithTax' => round($item->getBasePriceInclTax(), 2),
                'priceWithoutTax' => round($item->getBasePrice(), 2),
                'discountAmount' => round($item->getBaseDiscountAmount() / $item->getQty(), 2),
                'quantity' => round($item->getQty(), 0),
                'weight' => $item->getWeight(),
                'sku' => $item->getSku(),
                'categories' => $product->getCategoryIds(),
                'attributes' => $this->productAttributesService->getAttributes($product),
            ];
        }

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
                $weightUnits = 'lb';
                break;
            case \Zend_Measure_Weight::KILOGRAM:
                $weightUnits = 'kg';
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
                $dimensionUnits = 'in';
                break;
            case \Zend_Measure_Length::CENTIMETER:
                $dimensionUnits = 'cm';
                break;
            default:
                throw new \InvalidArgumentException('Invalid dimension units');
        }

        return $dimensionUnits;
    }

    /**
     * @param \Magento\Sales\Model\Order\Address|null $address
     * @return \Magento\Directory\Model\Region|null
     */
    private function getRegionModel($address)
    {
        if (!$address) {
            return null;
        }
        $regionId = (!$address->getRegionId() && is_numeric($address->getRegion())) ?
            $address->getRegion() :
            $address->getRegionId();
        $model = $this->regionFactory->create()->load($regionId);
        if ($model->getCountryId() == $address->getCountryId()) {
            return $model;
        }

        return null;
    }
}
