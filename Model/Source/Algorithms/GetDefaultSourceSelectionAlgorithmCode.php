<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Source\Algorithms;

use Calcurates\ModuleMagento\Model\Config;
use Calcurates\ModuleMagento\Model\Config\Source\DefaultSourceSelectionAlgorithm;
use Magento\InventorySourceSelectionApi\Api\GetDefaultSourceSelectionAlgorithmCodeInterface;

// fix for correct working of \Magento\Setup\Module\Di\Code\Reader\FileClassScanner
if (true) {
    if (!interface_exists(\Magento\InventorySourceSelectionApi\Api\GetDefaultSourceSelectionAlgorithmCodeInterface::class)) {
        class_alias(
            \Calcurates\ModuleMagento\Api\Fake\GetDefaultSourceSelectionAlgorithmCodeInterface::class,
            \Magento\InventorySourceSelectionApi\Api\GetDefaultSourceSelectionAlgorithmCodeInterface::class
        );
    }
}

class GetDefaultSourceSelectionAlgorithmCode implements GetDefaultSourceSelectionAlgorithmCodeInterface
{
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function execute(): string
    {
        $selectedAlgorithm = $this->config->getSourceSelectionAlgorithm();
        return $selectedAlgorithm == DefaultSourceSelectionAlgorithm::NOT_AVAILABLE_SOURCE_SELECTION
            ? 'calcurates'
            : $selectedAlgorithm;
    }
}
