<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Source;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\Order;
use Calcurates\ModuleMagento\Model\Carrier\Method\CarrierData;

class ShipmentServiceRetriever
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ShippingMethodManager
     */
    private $shippingMethodManager;

    /**
     * ShipmentServiceRetriever constructor.
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer, ShippingMethodManager $shippingMethodManager)
    {
        $this->serializer = $serializer;
        $this->shippingMethodManager = $shippingMethodManager;
    }

    /**
     * @param Order $order
     * @param string $requestedSourceCode
     * @return mixed
     */
    public function retrieve($order, $requestedSourceCode)
    {
        try {
            $carrierData = $this->shippingMethodManager->getCarrierData(
                $order->getShippingMethod(),
                $order->getShippingDescription(),
                $order->getData(CustomSalesAttributesInterface::CARRIER_SOURCE_CODE_TO_SERVICE)
            );
        } catch (\InvalidArgumentException $e) {
            $carrierData = null;
        }
        if (!$carrierData) {
            return "";
        }
        try {
            $carrierServicesToOrigins = $order->getData(
                CustomSalesAttributesInterface::CARRIER_SOURCE_CODE_TO_SERVICE
            );
            $carrierServicesToOrigins = $this->serializer->unserialize($carrierServicesToOrigins);
        } catch (\InvalidArgumentException $e) {
            $carrierServicesToOrigins = [];
        }
        if ($carrierData instanceof CarrierData) {
            $carrierId = $carrierData->getCarrierId();
            $shippingServices = $carrierData->getServiceIdsString();
            $shippingServicesArray = $carrierData->getServiceIds();
            $sourceCodesToServices = $carrierServicesToOrigins[$carrierId][$shippingServices] ?? [];
            return $sourceCodesToServices[$requestedSourceCode] ?? current($shippingServicesArray);
        } elseif (is_array($carrierData)) {
            $shippingServicesArray = [];
            foreach ($carrierData as $carrier) {
                $carrierId = $carrier->getCarrierId();
                $shippingServices = $carrier->getServiceIdsString();
                $shippingServicesArray = $carrier->getServiceIds();

                $sourceCodesToServices = $carrierServicesToOrigins[$carrierId][$shippingServices] ?? [];
                if (array_key_exists($requestedSourceCode, $sourceCodesToServices)) {
                    return $sourceCodesToServices[$requestedSourceCode];
                }
            }
            return current($shippingServicesArray);
        }
    }
}
