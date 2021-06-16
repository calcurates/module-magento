<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\SalesData\QuoteData;

interface GetQuoteDataInterface
{
    /**
     * @param int $quoteId
     * @return \Calcurates\ModuleMagento\Api\Data\QuoteDataInterface|null
     */
    public function get(int $quoteId): ?\Calcurates\ModuleMagento\Api\Data\QuoteDataInterface;
}
