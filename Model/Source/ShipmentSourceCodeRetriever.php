<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Source;

use Magento\Sales\Model\Order\Shipment;

class ShipmentSourceCodeRetriever
{
    /**
     * @param Shipment $shipment
     * @return string|null
     */
    public function retrieve($shipment)
    {
        $sourceCode = '';
        if (method_exists($shipment, 'getExtensionAttributes')) {
            $extensionAttributes = $shipment->getExtensionAttributes();
            if (method_exists($extensionAttributes, 'getSourceCode')) {
                $sourceCode = $extensionAttributes->getSourceCode();
            }
        }

        return $sourceCode;
    }
}
