<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Request;

use Calcurates\ModuleMagento\Model\Source\GetSourceCodesPerSkus;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Group;
use Magento\Store\Model\StoreManagerInterface;

class ProductRateRequestBuilder
{
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
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    public function __construct(
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        GetSourceCodesPerSkus $getSourceCodesPerSkus,
        ProductAttributesService $productAttributesService,
        CustomerRepositoryInterface $customerRepository,
        CustomerFactory $customerFactory
    ) {
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->getSourceCodesPerSkus = $getSourceCodesPerSkus;
        $this->productAttributesService = $productAttributesService;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param int[] $productIds
     * @param int $customerId
     * @param int $storeId
     * @param string[]|null $shipTo
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function build(array $productIds, int $customerId, int $storeId, ?array $shipTo = null): array
    {
        $store = $this->storeManager->getStore($storeId);
        $websiteCode = (string)$this->storeManager->getWebsite($store->getWebsiteId())->getCode();

        $customerGroupId = Group::NOT_LOGGED_IN_ID;
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            if ($customer instanceof CustomerInterface) {
                $customer = $this->customerFactory->create()->updateData($customer);
            }
            $customerGroupId = $customer->getGroupId();
            $address = $customer->getDefaultShippingAddress();
            if (!$shipTo && $address) {
                $shipTo = [
                    'country' => $address->getCountryId(),
                    'regionCode' => $address->getRegionId() ? $address->getRegionCode() : null,
                    'regionName' => $address->getRegion(),
                    'postalCode' => $address->getPostcode(),
                    'city' => $address->getCity(),
                    'addressLine1' => $address->getStreetLine(1),
                    'addressLine2' => $address->getStreetLine(2),
                    'contactName' => $customer->getName(),
                    'companyName' => $address->getCompany(),
                    'contactPhone' => $address->getTelephone(),
                ];
            }
        }

        $apiRequestBody = [
            'promoCode' => '',
            'storeView' => $storeId,
            'customerGroup' => (string)$customerGroupId,
            'shipTo' => $shipTo,
            'products' => [],
        ];

        foreach ($productIds as $productId) {
            $product = $this->productRepository->getById($productId, false, $storeId, true);

            // @TODO: change implementation for configurables and bundles!
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $configurableChildIds = $product->getTypeInstance()->getUsedProductIds($product);

                if (!isset($configurableChildIds[0])) {
                    continue;
                }
                $product = $this->productRepository->getById($configurableChildIds[0], false, $storeId, true);
            }

            $priceModel = $product->getPriceModel();
            $productBasePrice = $priceModel->getBasePrice($product);

            $isVirtual = $product->isVirtual();

            $itemsSourceCodes = $this->getSourceCodesPerSkus->execute([$product->getSku()], $websiteCode);
            $apiRequestBody['products'][] = [
                'sku' => $product->getSku(),
                'isVirtual' => $isVirtual,
                'priceWithTax' => round($productBasePrice, 2),
                'priceWithoutTax' => round($productBasePrice, 2),
                'discountAmount' => round(0.00, 2),
                'quantity' => 1,
                'weight' => $isVirtual ? 0 : $product->getWeight(),
                'categories' => $product->getCategoryIds(),
                'attributes' => $this->productAttributesService->getAttributes($product),
                'inventories' => $itemsSourceCodes[$product->getSku()] ?? []
            ];
        }

        return $apiRequestBody;
    }
}
