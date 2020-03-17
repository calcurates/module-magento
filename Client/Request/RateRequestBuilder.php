<?php

namespace Calcurates\ModuleMagento\Client\Request;

use Calcurates\ModuleMagento\Model\Source\GetSourceCodesPerSkus;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region as RegionResource;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;

class RateRequestBuilder
{
    /**
     * @var array
     */
    private $regionNamesCache = [];

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var RegionResource
     */
    private $regionResource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var GetSourceCodesPerSkus
     */
    private $getSourceCodesPerSkus;

    /**
     * RateRequestBuilder constructor.
     * @param RegionFactory $regionFactory
     * @param RegionResource $regionResource
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param GetSourceCodesPerSkus $getSourceCodesPerSkus
     */
    public function __construct(
        RegionFactory $regionFactory,
        RegionResource $regionResource,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        GetSourceCodesPerSkus $getSourceCodesPerSkus
    ) {
        $this->regionFactory = $regionFactory;
        $this->regionResource = $regionResource;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->getSourceCodesPerSkus = $getSourceCodesPerSkus;
    }

    /**
     * @param RateRequest $request
     * @param Item[] $items
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function build(RateRequest $request, array $items)
    {
        $streetArray = explode("\n", $request->getDestStreet());

        $apiRequestBody = [
            'country' => $request->getDestCountryId(),
            'regionCode' => $request->getDestRegionId() ? $request->getDestRegionCode() : null,
            'regionName' => $this->getRegionCodeById($request->getDestRegionId()) ?: $request->getDestRegionCode(),
            'postalCode' => $request->getDestPostcode(),
            'city' => $request->getDestCity(),
            'addressLine1' => $streetArray[0],
            'addressLine2' => $streetArray[1] ?? '',
            'customerGroup' => '',
            'promo' => '',
            'products' => [],
            // storeId in $request - from quote, and not correct if we open store via store url
            // setting "Use store codes in URL"
            'storeView' => $this->storeManager->getStore()->getId(),
        ];

        $quote = current($items)->getQuote();
        $customer = $quote->getCustomer();
        $apiRequestBody = array_merge($apiRequestBody, $this->getCustomerData($quote));

        if ($customer->getId()) {
            $apiRequestBody['customerGroup'] = $customer->getGroupId();
        }

        $itemsSkus = [];
        foreach ($items as $item) {
            $itemsSkus[$item->getSku()] = $item->getSku();
        }

        $itemsSkus = array_values($itemsSkus);
        $itemsSourceCodes = $this->getSourceCodesPerSkus->execute($itemsSkus);

        foreach ($items as $item) {
            $apiRequestBody['products'][] = [
                'priceWithTax' => round($item->getBasePriceInclTax(), 2),
                'priceWithoutTax' => round($item->getBasePrice(), 2),
                'discountAmount' => round($item->getBaseDiscountAmount(), 2),
                'quantity' => $item->getQty(),
                'weight' => $item->getWeight(),
                'sku' => $item->getSku(),
                'categories' => $item->getProduct()->getCategoryIds(),
                'attributes' => $this->getAttributes($item),
                'sources' => $itemsSourceCodes[$item->getSku()] ?? []
            ];
        }

        return $apiRequestBody;
    }

    /**
     * @param string $regionId
     *
     * @return string|null
     */
    private function getRegionCodeById($regionId)
    {
        if (!$regionId) {
            return null;
        }

        if (!empty($this->regionNamesCache[$regionId])) {
            return $this->regionNamesCache[$regionId];
        }

        $regionInstance = $this->regionFactory->create();
        $this->regionResource->load($regionInstance, $regionId);

        return $this->regionNamesCache[$regionId] = $regionInstance->getName();
    }

    /**
     * Collect customer information from shipping address
     *
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return array
     */
    private function getCustomerData(\Magento\Quote\Model\Quote $quote)
    {
        $customerData = [
            'contactName' => '',
            'companyName' => '',
            'contactPhone' => '',
        ];
        $shipAddress = $quote->getShippingAddress();

        $customerData['contactName'] = $shipAddress->getPrefix() . ' ';
        $customerData['contactName'] .= $shipAddress->getFirstname() ? $shipAddress->getFirstname() . ' ' : '';
        $customerData['contactName'] .= $shipAddress->getMiddlename() ? $shipAddress->getMiddlename() . ' ' : '';
        $customerData['contactName'] = trim($customerData['contactName'] . $shipAddress->getLastname());

        $customerData['companyName'] = $shipAddress->getCompany();
        $customerData['contactPhone'] = $shipAddress->getTelephone();

        return $customerData;
    }

    /**
     * @param Item $item
     * @return array
     */
    private function getAttributes(Item $item)
    {
        $product = $this->productRepository->get($item->getSku());
        $data = [];
        foreach ($product->getData() as $key => $value) {
            if (is_object($value)) {
                continue;
            }

            $data[$key] = $value;
        }
        foreach ($product->getCustomAttributes() as $customAttribute) {
            $data[$customAttribute->getAttributeCode()] = $customAttribute->getValue();
        }

        return $data;
    }
}
