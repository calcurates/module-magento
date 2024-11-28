<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

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
