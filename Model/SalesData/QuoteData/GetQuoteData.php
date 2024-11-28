<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\SalesData\QuoteData;

use Calcurates\ModuleMagento\Api\Data\QuoteDataInterface;
use Calcurates\ModuleMagento\Api\Data\QuoteDataInterfaceFactory;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\GetQuoteDataInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\QuoteData;

class GetQuoteData implements GetQuoteDataInterface
{
    /**
     * @var QuoteDataInterfaceFactory
     */
    private $quoteDataFactory;

    /**
     * @var QuoteData
     */
    private $resource;

    public function __construct(QuoteDataInterfaceFactory $quoteDataFactory, QuoteData $resource)
    {
        $this->quoteDataFactory = $quoteDataFactory;
        $this->resource = $resource;
    }

    /**
     * @param int $quoteId
     * @return QuoteDataInterface|null
     */
    public function get(int $quoteId): ?QuoteDataInterface
    {
        $model = $this->quoteDataFactory->create();

        $this->resource->load($model, $quoteId, QuoteDataInterface::QUOTE_ID);

        return $model->getId() ? $model : null;
    }
}
