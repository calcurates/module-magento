<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\ResourceModel;

use Calcurates\ModuleMagento\Api\Data\QuoteDataInterface;

class QuoteData extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const TABLE_NAME = 'calcurates_quote_data';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, QuoteDataInterface::ID);
    }
}
