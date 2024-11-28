<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Calcurates\ModuleMagento\Client\Response\ResponseProcessorInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Shipping\Model\Rate\Result;

class OriginProcessor implements ResponseProcessorInterface
{
    /**
     * @param Result $result
     * @param array $response
     * @param CartInterface $quote
     * @return void
     */
    public function process(Result $result, array &$response, CartInterface $quote): void
    {
        $quoteItemIdToSourceCode = [];
        foreach ($response['origins'] as $origin) {
            $sourceCode = $origin['origin']['syncedTargetOriginCode'] ?? null;
            if ($sourceCode === null) {
                continue;
            }
            foreach ($origin['products'] as $product) {
                $quoteItemId = $product['quoteItemId'];
                $quoteItemIdToSourceCode[$quoteItemId] = $sourceCode;
            }
        }

        foreach ($quote->getAllItems() as $quoteItem) {
            /** @var Item $quoteItem */
            if (array_key_exists($quoteItem->getId(), $quoteItemIdToSourceCode)) {
                $quoteItem->setData(CustomSalesAttributesInterface::SOURCE_CODE, $quoteItemIdToSourceCode[$quoteItem->getId()]);
            }
        }
    }
}
