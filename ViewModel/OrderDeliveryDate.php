<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\ViewModel;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Calcurates\ModuleMagento\Api\Data\OrderDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\OrderData\GetOrderDataInterface;
use Calcurates\ModuleMagento\Model\Carrier\DeliveryDateFormatter;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;

class OrderDeliveryDate implements ArgumentInterface
{
    /**
     * @var DeliveryDateFormatter
     */
    private $deliveryDateFormatter;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var GetOrderDataInterface
     */
    private $getOrderData;

    public function __construct(
        DeliveryDateFormatter $deliveryDateFormatter,
        SerializerInterface $serializer,
        GetOrderDataInterface $getOrderData
    ) {
        $this->deliveryDateFormatter = $deliveryDateFormatter;
        $this->serializer = $serializer;
        $this->getOrderData = $getOrderData;
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function getDeliveryDatesString(OrderInterface $order): string
    {
        $deliveryDatesSerialized = $order->getData(CustomSalesAttributesInterface::DELIVERY_DATES);
        if (!$deliveryDatesSerialized) {
            return '';
        }

        $deliveryDatesData = $this->serializer->unserialize($deliveryDatesSerialized);

        return $this->deliveryDateFormatter->formatDeliveryDate(
            $deliveryDatesData['from'] ?? null,
            $deliveryDatesData['to'] ?? null
        );
    }

    /**
     * @param OrderInterface $order
     * @return OrderDataInterface|null
     */
    public function getOrderData(OrderInterface $order): ?OrderDataInterface
    {
        return $this->getOrderData->get((int)$order->getId());
    }

    /**
     * @param string $date
     * @return string
     */
    public function formatDate(string $date): string
    {
        [$dateObject] = $this->deliveryDateFormatter->prepareDates($date, null);

        return $this->deliveryDateFormatter->formatSingleDate($dateObject);
    }
}
