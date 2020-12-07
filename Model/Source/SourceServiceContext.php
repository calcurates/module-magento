<?php

namespace Calcurates\ModuleMagento\Model\Source;

use Magento\Framework\Module\Manager;

class SourceServiceContext
{
    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(Manager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @return bool
     */
    public function isInventoryEnabled(): bool
    {
        return $this->moduleManager->isEnabled('Magento_Inventory') &&
            $this->moduleManager->isEnabled('Magento_InventoryApi');
    }

    public function isSourceSelectionEnabled(): bool
    {
        return $this->moduleManager->isEnabled('Magento_InventorySourceSelection') &&
            $this->moduleManager->isEnabled('Magento_InventorySourceSelectionApi');
    }
}
