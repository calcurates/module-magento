<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */
namespace Calcurates\ModuleMagento\Model\Shipment;

use Calcurates\ModuleMagento\Api\Data\ShippingDataInterface;
use Calcurates\ModuleMagento\Api\Data\ShippingDataInterfaceFactory;
use Calcurates\ModuleMagento\Api\Shipping\ShippingDataResolverInterface;
use Calcurates\ModuleMagento\Model\Source\ShipmentServiceRetriever;
use Calcurates\ModuleMagento\Model\Source\ShipmentSourceCodeRetriever;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\Data\ShipmentInterface;

class ShippingDataResolver implements ShippingDataResolverInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ShipmentSourceCodeRetriever
     */
    private $shipmentSourceCodeRetriever;

    /**
     * @var ShipmentServiceRetriever
     */
    private $shipmentServiceRetriever;

    /**
     * @var ShippingDataInterfaceFactory
     */
    private $shippingDataInterfaceFactory;

    /**
     * SourceAndServiceResolver constructor.
     * @param RequestInterface $request
     * @param ShipmentSourceCodeRetriever $shipmentSourceCodeRetriever
     * @param ShipmentServiceRetriever $shipmentServiceRetriever
     * @param ShippingDataInterfaceFactory $shippingDataInterfaceFactory
     */
    public function __construct(
        RequestInterface $request,
        ShipmentSourceCodeRetriever $shipmentSourceCodeRetriever,
        ShipmentServiceRetriever $shipmentServiceRetriever,
        ShippingDataInterfaceFactory $shippingDataInterfaceFactory
    ) {
        $this->request = $request;
        $this->shipmentSourceCodeRetriever = $shipmentSourceCodeRetriever;
        $this->shipmentServiceRetriever = $shipmentServiceRetriever;
        $this->shippingDataInterfaceFactory = $shippingDataInterfaceFactory;
    }

    /**
     * @param ShipmentInterface $shipment
     * @return ShippingDataInterface
     */
    public function getShippingData(ShipmentInterface $shipment)
    {
        $shippingServiceId = $this->request->getParam('calcuratesShippingServiceId');
        $sourceCode = $this->shipmentSourceCodeRetriever->retrieve($shipment);
        if (!$shippingServiceId) {
            $shippingServiceId = $this->shipmentServiceRetriever->retrieve(
                $shipment->getOrder(),
                $sourceCode
            );
        }

        /** @var ShippingDataInterface $shippingData */
        $shippingData = $this->shippingDataInterfaceFactory->create();

        $shippingData->setSourceCode($sourceCode)
            ->setShippingServiceId($shippingServiceId);

        return $shippingData;
    }
}
