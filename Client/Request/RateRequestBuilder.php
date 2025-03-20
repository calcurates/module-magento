<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Request;

use Calcurates\ModuleMagento\Model\Source\GetSourceCodesPerSkus;
use Calcurates\ModuleMagento\Plugin\Model\Shipping\ShippingAddEstimateFlagToRequestPlugin;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region as RegionResource;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Bundle\Model\Product\Type as Bundle;

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
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * RateRequestBuilder constructor.
     * @param RegionFactory $regionFactory
     * @param RegionResource $regionResource
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param GetSourceCodesPerSkus $getSourceCodesPerSkus
     * @param ProductAttributesService $productAttributesService
     * @param ObjectManagerInterface $objectManager
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        RegionFactory $regionFactory,
        RegionResource $regionResource,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        GetSourceCodesPerSkus $getSourceCodesPerSkus,
        ProductAttributesService $productAttributesService,
        ObjectManagerInterface $objectManager,
        ModuleManager $moduleManager
    ) {
        $this->regionFactory = $regionFactory;
        $this->regionResource = $regionResource;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->getSourceCodesPerSkus = $getSourceCodesPerSkus;
        $this->productAttributesService = $productAttributesService;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
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
        $streetArray = explode("\n", (string)$request->getDestStreet());
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
            'extraFee' => [],
            'customerGroup' => (string)($customer->getGroupId() ?: 0),
            'promo' => null,
            'products' => [],
            // storeId in $request - from quote, and not correct if we open store via store url
            // setting "Use store codes in URL"
            'storeView' => $storeId,
            'promoCode' => (string)$quote->getCouponCode(),
            'estimate' => $estimate,
            'vatNumber' => $request->getVatId(),
        ];

        $itemsSkus = [];
        foreach ($items as $item) {
            $itemsSkus[$item->getSku()] = $item->getSku();
            $itemsSkus[$item->getProduct()->getData('sku')] = $item->getProduct()->getData('sku');
            if ($item->getProductType() === Bundle::TYPE_CODE) {
                foreach ($item->getChildren() as $childItem) {
                    if ($childItem && $childItem->getSku()) {
                        $itemsSkus[$childItem->getSku()] = $childItem->getSku();
                    }
                }
            }
        }

        $itemsSkus = array_values($itemsSkus);
        $itemsSourceCodes = $this->getSourceCodesPerSkus->execute($itemsSkus, $websiteCode);

        if ($this->moduleManager->isEnabled('Amasty_Extrafee')) {
            $feeQuoteCollectionFactory = $this->objectManager
                ->get(\Amasty\Extrafee\Model\ResourceModel\ExtrafeeQuote\CollectionFactory::class);
            $feesQuoteCollection = $feeQuoteCollectionFactory->create()
                ->addFieldToFilter('option_id', ['neq' => '0'])
                ->addFieldToFilter('quote_id', $quote->getId());
            foreach ($feesQuoteCollection->getItems() as $key => $feeOption) {
                $apiRequestBody['extraFee'][] = [
                    'id' => $feeOption->getOptionId(),
                    'amount' => $feeOption->getBaseFeeAmount()
                ];
            }
        }

        foreach ($items as $item) {
            $attributedProductId = $item->getProductId();

            // for configurable - load all attributes from child
            if ($item->getProductType() === Configurable::TYPE_CODE) {
                $childItem = current($item->getChildren());
                if ($childItem && $childItem->getProductId()) {
                    $attributedProductId = $childItem->getProductId();
                }
            }
            $bundleItemInventories = [];
            if ($item->getProductType() === Bundle::TYPE_CODE) {
                foreach ($item->getChildren() as $childItem) {
                    if ($childItem->getSku() && isset($itemsSourceCodes[$childItem->getSku()])) {
                        foreach ($itemsSourceCodes[$childItem->getSku()] as $source) {
                            if (!isset($bundleItemInventories[$source['source']])
                                || $bundleItemInventories[$source['source']] > $source['quantity']
                            ) {
                                $bundleItemInventories[$source['source']] = $source['quantity'];
                            }
                        }
                    }
                }
                if ($bundleItemInventories) {
                    $resultedInventories = [];
                    foreach ($bundleItemInventories as $srouceCode => $quantity) {
                        $resultedInventories[] = [
                            'source' => $srouceCode,
                            'quantity' => $quantity
                        ];
                    }
                    $bundleItemInventories = $resultedInventories;
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

            $isVirtual = (bool) $item->getIsVirtual();

            if (!isset($itemsSourceCodes[$attributedProduct->getSku()]) && isset($bundleItemInventories)) {
                $inventories = $bundleItemInventories;
            } else {
                $inventories = $itemsSourceCodes[$attributedProduct->getSku()] ?? [];
            }
            $apiRequestBody['products'][] = [
                'quoteItemId' => $item->getItemId() ?? $item->getQuoteItemId(),
                'priceWithTax' => round((float) $item->getBasePriceInclTax(), 2),
                'priceWithoutTax' => round((float) $item->getBasePrice(), 2),
                'discountAmount' => round((float) $item->getBaseDiscountAmount() / (float) $item->getQty(), 2),
                'quantity' => ceil((float) $item->getQty()),
                'weight' => $isVirtual ? 0 : $item->getWeight(),
                'sku' => $item->getSku(),
                'isVirtual' => $isVirtual,
                'attributes' => $attributes,
                'inventories' => $inventories
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
