<?php

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
