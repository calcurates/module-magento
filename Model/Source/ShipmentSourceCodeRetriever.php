<?php

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
