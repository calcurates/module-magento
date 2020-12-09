<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\CheckoutConverter;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Calcurates\ModuleMagento\Model\Carrier\Method\CarrierData;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class ConvertPackages
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param Quote $quote
     * @param Order $order
     * @param CarrierData $carrierData
     */
    public function convert(Quote $quote, Order $order, CarrierData $carrierData): void
    {
        $packages = $quote->getData(CustomSalesAttributesInterface::CARRIER_PACKAGES);
        if (!$packages) {
            return;
        }

        $packages = $this->serializer->unserialize($packages);
        $packages = $packages[$carrierData->getCarrierId()][$carrierData->getServiceIdsString()] ?? null;
        if (!$packages) {
            return;
        }

        $quoteItemIdToOrderItemId = $this->getOrderItemIdToQuoteItemIdMap($order);
        $orderPackages = [];
        foreach ($packages as $package) {
            $orderItemsWithQtys = [];
            foreach ($package['products'] as $product) {
                $orderItemId = $quoteItemIdToOrderItemId[$product['quoteItemId']] ?? null;

                if (isset($orderItemsWithQtys[$orderItemId])) {
                    $orderItemsWithQtys[$orderItemId]['qty'] += 1;
                } else {
                    $orderItemsWithQtys[$orderItemId] = [
                        'item_id' => $orderItemId,
                        'qty' => 1
                    ];
                }
            }

            $package['products'] = array_values($orderItemsWithQtys);
            $orderPackages[] = $package;
        }

        if ($orderPackages) {
            $orderPackages = $this->serializer->serialize($orderPackages);
            $order->setData(CustomSalesAttributesInterface::CARRIER_PACKAGES, $orderPackages);
        }
    }

    /**
     * @param Order $order
     * @return array
     */
    private function getOrderItemIdToQuoteItemIdMap(Order $order): array
    {
        $map = [];
        foreach ($order->getAllItems() as $orderItem) {
            $map[$orderItem->getQuoteItemId()] = $orderItem->getItemId();
        }

        return $map;
    }
}
