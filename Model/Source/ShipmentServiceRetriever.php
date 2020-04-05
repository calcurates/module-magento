<?php

namespace Calcurates\ModuleMagento\Model\Source;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\Order;

class ShipmentServiceRetriever
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * ShipmentServiceRetriever constructor.
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param Order $order
     * @param string $requestedSourceCode
     * @return mixed
     */
    public function retrieve($order, $requestedSourceCode)
    {
        $shippingMethod = $order->getShippingMethod(false);
        list(, $carrierId, $shippingServices) = explode('_', $shippingMethod);
        $shippingServicesArray = explode(',', $shippingServices);

        try {
            $carrierServicesToOrigins = $order->getData(CustomSalesAttributesInterface::CARRIER_SOURCE_CODE_TO_SERVICE);
            $carrierServicesToOrigins = $this->serializer->unserialize($carrierServicesToOrigins);
        } catch (\InvalidArgumentException $e) {
            $carrierServicesToOrigins = [];
        }

        $sourceCodesToServices = $carrierServicesToOrigins[$carrierId][$shippingServices] ?? [];

        return $sourceCodesToServices[$requestedSourceCode] ?? current($shippingServicesArray);
    }
}
