<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Strategy;

use Magento\Quote\Model\Quote;
use Magento\Shipping\Model\Rate\Result;

interface RatesStrategyInterface
{
    /**
     * @param array $apiRequestBody
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     */
    public function getResponse(array $apiRequestBody, $storeId): array;

    /**
     * @param array $response
     * @param Quote $quote
     * @return mixed
     */
    public function processResponse(array $response, Quote $quote): Result;
}
