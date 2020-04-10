<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Source\Algorithms;

use Calcurates\ModuleMagento\Model\Source\SourceServiceContext;
use Magento\InventorySourceSelectionApi\Api\GetDefaultSourceSelectionAlgorithmCodeInterface;

if (!SourceServiceContext::doesSourceExist()) {
    class_alias(
        \Calcurates\ModuleMagento\Api\Fake\GetDefaultSourceSelectionAlgorithmCodeInterface::class,
        \Magento\InventorySourceSelectionApi\Api\GetDefaultSourceSelectionAlgorithmCodeInterface::class
    );
}

class GetDefaultSourceSelectionAlgorithmCode implements GetDefaultSourceSelectionAlgorithmCodeInterface
{
    /**
     * @inheritDoc
     */
    public function execute(): string
    {
        return 'calcurates';
    }
}
