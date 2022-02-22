<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor\SplitCheckout;

use Calcurates\ModuleMagento\Client\Response\ResponseProcessorInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Shipping\Model\Rate\Result;
use Calcurates\ModuleMagento\Model\Data\MetaRateData;

class ProductProcessor implements ResponseProcessorInterface
{
    private $metaRateData;

    public function __construct(
        MetaRateData $metaRateData
    ) {
        $this->metaRateData = $metaRateData;
    }

    public function process(Result $result, array &$response, CartInterface $quote): void
    {
        $products = [];
        foreach ($response['products'] as $product) {
            $products[] = $product['quoteItemId'];
        }
        $this->metaRateData->setProductData($response['origin']['id'], $products);
    }
}
