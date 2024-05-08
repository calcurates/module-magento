<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor\SplitCheckout;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Calcurates\ModuleMagento\Client\Response\ResponseProcessorInterface;
use Calcurates\ModuleMagento\Model\Data\MetaRateData;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Shipping\Model\Rate\Result;

class ProductProcessor implements ResponseProcessorInterface
{
    /**
     * @var MetaRateData
     */
    private $metaRateData;

    /**
     * @param MetaRateData $metaRateData
     */
    public function __construct(
        MetaRateData $metaRateData
    ) {
        $this->metaRateData = $metaRateData;
    }

    /**
     * @param Result $result
     * @param array $response
     * @param CartInterface $quote
     * @return void
     */
    public function process(Result $result, array &$response, CartInterface $quote): void
    {
        $products = [];
        $qtys = [];
        foreach ($response['products'] as $product) {
            $products[] = $product['quoteItemId'];
            if (isset($product['quantity'])) {
                $qtys[] = [$product['quoteItemId'] => $product['quantity']];
            }
        }

        foreach ($quote->getAllItems() as $quoteItem) {
            if (in_array($quoteItem->getId(), $products)) {
                $quoteItem->setData(
                    CustomSalesAttributesInterface::SOURCE_CODE,
                    $response['origin']['syncedTargetOriginCode'] ?? null
                );
            }
        }

        $this->metaRateData->setProductData($response['origin']['id'], $products);
        $this->metaRateData->setProductQtys($response['origin']['id'], $qtys);
    }
}
