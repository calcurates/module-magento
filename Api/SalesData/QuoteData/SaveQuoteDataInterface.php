<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\SalesData\QuoteData;

interface SaveQuoteDataInterface
{
    /**
     * @param \Calcurates\ModuleMagento\Api\Data\QuoteDataInterface $calcuratesQuote
     * @return \Calcurates\ModuleMagento\Api\Data\QuoteDataInterface
     */
    public function save(
        \Calcurates\ModuleMagento\Api\Data\QuoteDataInterface $calcuratesQuote
    ): \Calcurates\ModuleMagento\Api\Data\QuoteDataInterface;
}
