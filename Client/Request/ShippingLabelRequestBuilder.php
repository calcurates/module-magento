<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client\Request;

use Calcurates\ModuleMagento\Api\Shipping\ShippingDataResolverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Directory\Model\RegionFactory;

class ShippingLabelRequestBuilder
{
    /**
     * @var ShippingDataResolverInterface
     */
    private $shippingDataResolver;

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
     * ShippingLabelRequestBuilder constructor.
     * @param ShippingDataResolverInterface $shippingDataResolver
     * @param RegionFactory $regionFactory
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ShippingDataResolverInterface $shippingDataResolver,
        RegionFactory $regionFactory,
        ProductRepositoryInterface $productRepository,
        ProductAttributesService $productAttributesService
    ) {
        $this->shippingDataResolver = $shippingDataResolver;
        $this->regionFactory = $regionFactory;
        $this->productRepository = $productRepository;
        $this->productAttributesService = $productAttributesService;
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
        $shippingAddress = $request->getOrderShipment()->getOrder()->getShippingAddress();
        $regionModel = $this->getRegionModel($shippingAddress);

        $apiRequestBody = [
            'service' => $shippingData->getShippingServiceId(),
            'source' => $shippingData->getSourceCode(),
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
                'discountAmount' => round($item->getBaseDiscountAmount(), 2),
                'quantity' => $item->getQty(),
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
