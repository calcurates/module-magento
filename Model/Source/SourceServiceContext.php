<?php

namespace Calcurates\ModuleMagento\Model\Source;

class SourceServiceContext
{
    /**
     * @return bool
     */
    public static function doesSourceExist()
    {
        return interface_exists(\Magento\InventoryApi\Api\SourceRepositoryInterface::class);
    }
}
