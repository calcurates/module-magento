<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client\Request;

use Calcurates\ModuleMagento\Api\Data\TaxIdentifierInterface;
use Calcurates\ModuleMagento\Model\Catalog\Product\Attribute\Resolver\HsCodeAttributeResolver;
use Calcurates\ModuleMagento\Model\Measure;
use Calcurates\ModuleMagento\Model\Source\ShipmentSourceCodeRetriever;
use InvalidArgumentException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\ShipmentItemInterface;
use Magento\Sales\Model\Order\Address;

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

    /**
     * @var HsCodeAttributeResolver
     */
    private $hsCodeAttributeResolver;

    /**
     * @param RegionFactory $regionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ProductAttributesService $productAttributesService
     * @param ShipmentSourceCodeRetriever $shipmentSourceCodeRetriever
     * @param HsCodeAttributeResolver $hsCodeAttributeResolver
     */
    public function __construct(
        RegionFactory $regionFactory,
        ProductRepositoryInterface $productRepository,
        ProductAttributesService $productAttributesService,
        ShipmentSourceCodeRetriever $shipmentSourceCodeRetriever,
        HsCodeAttributeResolver $hsCodeAttributeResolver
    ) {
        $this->regionFactory = $regionFactory;
        $this->productRepository = $productRepository;
        $this->productAttributesService = $productAttributesService;
        $this->shipmentSourceCodeRetriever = $shipmentSourceCodeRetriever;
        $this->hsCodeAttributeResolver = $hsCodeAttributeResolver;
    }

    /**
     * @param DataObject $request
     * @param bool $testLabel
     * @return array
     * @throws NoSuchEntityException
     */
    public function build(DataObject $request, $testLabel = false)
    {
        $shippingAddress = $request->getOrderShipment()->getOrder()->getShippingAddress();
        $regionModel = $this->getRegionModel($shippingAddress);

        $sourceCode = $this->shipmentSourceCodeRetriever->retrieve($request->getOrderShipment());

        $apiRequestBody = [
            'source' => $sourceCode,
            'serviceCode' => $request->getData('calcurates_service_code'),
            'carrierCode' => $request->getData('calcurates_carrier_code'),
            'providerCode' => $request->getData('calcurates_provider_code'),
            'insurance' => $request->getData('calcurates_service_insurance'),
            'shipDateUtc' => $request->getData('calcurates_shipping_date'),
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
            'validateAddress' => 'no_validation'
        ];

        foreach ($request['calcurates_tax_ids'] ?? [] as $taxId) {
            $apiRequestBody['taxIdentifiers'][] = [
                'identifierType' => $taxId[TaxIdentifierInterface::TYPE],
                'issuingAuthority' => $taxId[TaxIdentifierInterface::ISSUING_AUTHORITY],
                'taxableEntityType' => $taxId[TaxIdentifierInterface::ENTITY_TYPE],
                'value' => $taxId[TaxIdentifierInterface::VALUE]
            ];
        }

        $products = [];
        /** @var ShipmentItemInterface $item */
        foreach ($request->getOrderShipment()->getAllItems() as $item) {
            $product = $this->productRepository->getById($item->getProductId());
            $isVirtual = (bool) $item->getIsVirtual();
            $products[$item->getOrderItemId()] = [
                'priceWithTax' => round($item->getBasePriceInclTax() ?? 0, 2),
                'priceWithoutTax' => round($item->getBasePrice() ?? 0, 2),
                'discountAmount' => round($item->getBaseDiscountAmount() ?? 0 / $item->getQty(), 2),
                'quantity' => ceil((float) $item->getQty() ?? 0),
                'weight' => $isVirtual ? 0 : $item->getWeight(),
                'sku' => $item->getSku(),
                'isVirtual' => $isVirtual,
                'categories' => $product->getCategoryIds(),
                'attributes' => $this->productAttributesService->getAttributes($product),
            ];
        }

        $this->populateRequestBodyWithHsCodeAttributeValues($apiRequestBody, $request);

        foreach ($request->getPackages() as $package) {
            $rawPackage = [
                'weight' => [
                    'value' => $package['params']['weight'],
                    'unit' => $this->getWeightUnits($package['params']['weight_units']),
                ],
            ];
            if ($package['params']['dimension_units']) {
                $rawPackage['dimensions'] = [
                    'length' => $package['params']['length'],
                    'width' => $package['params']['width'],
                    'height' => $package['params']['height'],
                    'unit' => $this->getDimensionUnits($package['params']['dimension_units']),
                ];
            }
            if (isset($package['items']) && is_array($package['items'])) {
                foreach ($package['items'] as $itemId => $packageItem) {
                    if (isset($products[$itemId])) {
                        $packagedProduct = $products[$itemId];
                        $packagedProduct['quantity'] = $packageItem['qty'];
                        $packagedProduct['weight'] = $packageItem['weight'];
                        $rawPackage['products'][] = $packagedProduct;
                    }
                }
            }
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
            case Measure::pound():
                $weightUnits = 'lb';
                break;
            case Measure::kilogram():
                $weightUnits = 'kg';
                break;
            case Measure::ounce():
                $weightUnits = 'oz';
                break;
            case Measure::gram():
                $weightUnits = 'g';
                break;
            default:
                throw new InvalidArgumentException('Invalid weight units');
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
            case Measure::inch():
                $dimensionUnits = 'in';
                break;
            case Measure::centimeter():
                $dimensionUnits = 'cm';
                break;
            default:
                throw new InvalidArgumentException('Invalid dimension units');
        }

        return $dimensionUnits;
    }

    /**
     * @param Address|null $address
     * @return Region|null
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

    /**
     * @param array $apiRequestBody
     * @param DataObject $request
     */
    private function populateRequestBodyWithHsCodeAttributeValues(array &$apiRequestBody, DataObject $request)
    {
        $orderShipmentItemsIdToSkuMap = [];
        /** @var ShipmentItemInterface $item */
        foreach ($request->getOrderShipment()->getAllItems() as $item) {
            $orderShipmentItemsIdToSkuMap[$item->getOrderItemId()] = $item->getSku();
        }

        $hsCodeValuesToProductSkusList = [];
        foreach ($request->getPackages() as $package) {
            if (is_array($package['items'])) {
                foreach ($package['items'] as $item) {
                    if (isset($item['hs_code_value'])
                        && !empty($item['hs_code_value'])
                    ) {
                        $hsCodeValuesToProductSkusList[$orderShipmentItemsIdToSkuMap[$item['order_item_id']]] =
                            $item['hs_code_value'];
                    }
                }
            }
        }

        if (!empty($hsCodeValuesToProductSkusList)) {
            $hsCodeAttribute = $this->hsCodeAttributeResolver->resolveAttribute(
                (int)$request->getOrderShipment()->getStoreId()
            );
            foreach ($apiRequestBody['products'] as $key => $preparedProduct) {
                if (array_key_exists($preparedProduct['sku'], $hsCodeValuesToProductSkusList)) {
                    $apiRequestBody['products'][$key]['attributes'][$hsCodeAttribute->getAttributeCode()] =
                        $hsCodeValuesToProductSkusList[$preparedProduct['sku']];
                }
            }
        }
    }
}
