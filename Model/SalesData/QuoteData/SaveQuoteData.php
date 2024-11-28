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
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(QuoteDataInterface $calcuratesQuote): QuoteDataInterface
    {
        $this->resource->save($calcuratesQuote);

        return $calcuratesQuote;
    }
}
