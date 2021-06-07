<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\SalesData\QuoteData;

use Calcurates\ModuleMagento\Api\Data\QuoteDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\SaveQuoteDataInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\QuoteData;

class SaveQuoteData implements SaveQuoteDataInterface
{
    /**
     * @var QuoteData
     */
    private $resource;

    public function __construct(QuoteData $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param int $quoteId
     * @return \Calcurates\ModuleMagento\Api\Data\QuoteDataInterface|null
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(QuoteDataInterface $calcuratesQuote): QuoteDataInterface
    {
        $this->resource->save($calcuratesQuote);

        return $calcuratesQuote;
    }
}
