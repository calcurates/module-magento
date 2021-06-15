<?php
declare(strict_types=1);

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client\Request;

use Calcurates\ModuleMagento\Model\Source\GetSourceCodesPerSkus;
use Calcurates\ModuleMagento\Plugin\Model\Shipping\ShippingAddEstimateFlagToRequestPlugin;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
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
     * @var ProductAttributesService
     */
    private $productAttributesService;

    /**
     * RateRequestBuilder constructor.
     * @param RegionFactory $regionFactory
     * @param RegionResource $regionResource
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param GetSourceCodesPerSkus $getSourceCodesPerSkus
     * @param ProductAttributesService $productAttributesService
     */
    public function __construct(
        RegionFactory $regionFactory,
        RegionResource $regionResource,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        GetSourceCodesPerSkus $getSourceCodesPerSkus,
        ProductAttributesService $productAttributesService
    ) {
        $this->regionFactory = $regionFactory;
        $this->regionResource = $regionResource;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->getSourceCodesPerSkus = $getSourceCodesPerSkus;
        $this->productAttributesService = $productAttributesService;
    }

    /**
     * @param RateRequest $request
     * @param Item[] $items
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function build(RateRequest $request, array $items): array
    {
        $store = $this->storeManager->getStore();
        $storeId = $store->getId();
        $websiteCode = (string)$this->storeManager->getWebsite($store->getWebsiteId())->getCode();
        /**
         * @var $quote \Magento\Quote\Model\Quote
         */
        $quote = current($items)->getQuote();
        $customerData = $this->getCustomerData($quote);
        $streetArray = explode("\n", $request->getDestStreet());
        $customer = $quote->getCustomer();
        $estimate = (bool)$request->getData(ShippingAddEstimateFlagToRequestPlugin::IS_ESTIMATE_ONLY_FLAG);

        $apiRequestBody = [
            'shipTo' => [
                'country' => $request->getDestCountryId(),
                'regionCode' => $request->getDestRegionId() ? $request->getDestRegionCode() : null,
                'regionName' => $this->getRegionNameById($request->getDestRegionId()) ?: $request->getDestRegionCode(),
                'postalCode' => $request->getDestPostcode(),
                'city' => $request->getDestCity(),
                'addressLine1' => $streetArray[0],
                'addressLine2' => $streetArray[1] ?? '',
                'contactName' => $customerData['contactName'],
                'companyName' => $customerData['companyName'],
                'contactPhone' => $customerData['contactPhone'],
            ],
            'customerGroup' => $customer->getGroupId() ?: 0,
            'promo' => null,
            'products' => [],
            // storeId in $request - from quote, and not correct if we open store via store url
            // setting "Use store codes in URL"
            'storeView' => $storeId,
            'promoCode' => (string)$quote->getCouponCode(),
            'estimate' => $estimate
        ];

        $itemsSkus = [];
        foreach ($items as $item) {
            $itemsSkus[$item->getSku()] = $item->getSku();
        }

        $itemsSkus = array_values($itemsSkus);
        $itemsSourceCodes = $this->getSourceCodesPerSkus->execute($itemsSkus, $websiteCode);

        foreach ($items as $item) {
            $attributedProductId = $item->getProductId();

            // for configurable - load all attributes from child
            if ($item->getProductType() === Configurable::TYPE_CODE) {
                $childItem = current($item->getChildren());
                if ($childItem && $childItem->getProductId()) {
                    $attributedProductId = $childItem->getProductId();
                }
            }

            $attributedProduct = $this->productRepository->getById(
                $attributedProductId,
                false,
                $this->storeManager->getStore()->getId(),
                true
            );

            $attributes = $this->productAttributesService->getAttributes($attributedProduct);
            $attributes['category_ids'] = $item->getProduct()->getCategoryIds(); // get category ids always from parent

            $apiRequestBody['products'][] = [
                'quoteItemId' => $item->getItemId() ?? $item->getQuoteItemId(),
                'priceWithTax' => round($item->getBasePriceInclTax(), 2),
                'priceWithoutTax' => round($item->getBasePrice(), 2),
                'discountAmount' => round($item->getBaseDiscountAmount() / $item->getQty(), 2),
                'quantity' => round($item->getQty(), 0),
                'weight' => $item->getIsVirtual() ? 0 : $item->getWeight(),
                'sku' => $item->getSku(),
                'attributes' => $attributes,
                'inventories' => $itemsSourceCodes[$item->getSku()] ?? []
            ];
        }

        return $apiRequestBody;
    }

    /**
     * @param string $regionId
     *
     * @return string|null
     */
    private function getRegionNameById($regionId)
    {
        if (!$regionId) {
            return null;
        }

        if (!isset($this->regionNamesCache[$regionId])) {
            $regionInstance = $this->regionFactory->create();
            $this->regionResource->load($regionInstance, $regionId);
            $this->regionNamesCache[$regionId] = $regionInstance->getName();
        }

        return $this->regionNamesCache[$regionId];
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
}
