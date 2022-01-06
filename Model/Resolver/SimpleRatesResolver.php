<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Resolver;

use Calcurates\ModuleMagento\Api\Data\SimpleRateInterface;
use Calcurates\ModuleMagento\Api\EstimateShippingByProductsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class SimpleRatesResolver implements ResolverInterface
{
    /**
     * @var EstimateShippingByProductsInterface
     */
    private $estimateShippingByProducts;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        EstimateShippingByProductsInterface $estimateShippingByProducts,
        ProductRepositoryInterface $productRepository
    ) {
        $this->estimateShippingByProducts = $estimateShippingByProducts;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value|mixed|void
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $skus = $this->getSkus($args);
        $customerId = $context->getUserId();

        $productIds = [];
        foreach ($skus as $sku) {
            $product = $this->productRepository->get($sku, false, $storeId);
            $productIds[] = (int)$product->getId();
        }

        $simpleRates = $this->estimateShippingByProducts->estimate($productIds, $customerId, $storeId);

        $simpleRatesData = [];
        foreach ($simpleRates as $simpleRate) {
            $simpleRatesData[] = [
                SimpleRateInterface::RENDERED_TEMPLATE => $simpleRate->getRenderedTemplate(),
                SimpleRateInterface::NAME => $simpleRate->getName(),
                SimpleRateInterface::AMOUNT => $simpleRate->getAmount(),
                SimpleRateInterface::DELIVERY_DATE_FROM => $simpleRate->getDeliveryDateFrom(),
                SimpleRateInterface::DELIVERY_DATE_TO => $simpleRate->getDeliveryDateTo(),
                SimpleRateInterface::TEMPLATE => $simpleRate->getTemplate(),
                SimpleRateInterface::TYPE => $simpleRate->getType()
            ];
        }

        return ['items' => $simpleRatesData];
    }

    private function getSkus(?array $args): array
    {
        if (!isset($args['skus']) || !is_array($args['skus']) || count($args['skus']) === 0) {
            throw new GraphQlInputException(__('"skus" of Products should be specified'));
        }

        return $args['skus'];
    }
}
