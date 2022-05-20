<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Strategy;

use Magento\Shipping\Model\Rate\Result;

interface RatesStrategyInterface
{
    /**
     * @param $apiRequestBody
     * @param $storeId
     * @return array
     */
    public function getResponse($apiRequestBody, $storeId): array;

    /**
     * @param $response
     * @param $quote
     * @return mixed
     */
    public function processResponse($response, $quote): Result;
}
