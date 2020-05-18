<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Shipment;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\Order\Shipment;

class ShippingLabelSaver
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * ShippingLabelSaver constructor.
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param Shipment $orderShipment
     * @param array $data
     */
    public function addShippingLabelDataToShipment(Shipment $orderShipment, array $data)
    {
        $orderShipment->setData(CustomSalesAttributesInterface::LABEL_DATA, $this->serializer->serialize($data));
    }
}
