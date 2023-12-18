<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\CheckoutConverter;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\GetQuoteDataInterface;
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

    /**
     * @var GetQuoteDataInterface
     */
    private $getQuoteData;

    /**
     * @param SerializerInterface $serializer
     * @param GetQuoteDataInterface $getQuoteData
     */
    public function __construct(
        SerializerInterface $serializer,
        GetQuoteDataInterface $getQuoteData
    ) {
        $this->serializer = $serializer;
        $this->getQuoteData = $getQuoteData;
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

        $splitShipments = $this->getQuoteData->get((int)$quote->getId())->getSplitShipments();
        if (!empty($splitShipments) && !empty($orderPackages)) {
            $splitShipmentsIdx = [];
            foreach ($splitShipments as $shipment) {
                $splitShipmentsIdx[$shipment['origin']] = $shipment;
            }
            $orderItemIdToSku = $this->getOrderItemIdToSkuMap($order);
            foreach ($orderPackages as &$orderPackage) {
                $packageProducts = array_filter(
                    $orderPackage['products'],
                    static function ($product) use ($orderPackage, $orderItemIdToSku, $splitShipmentsIdx, $carrierData) {
                        list(, , $serviceIdString) = array_pad(explode(
                            '_',
                            $splitShipmentsIdx[$orderPackage['origin_id']]['method'] ?? '',
                            3
                        ), 3, '');
                        return $carrierData->getServiceIdsString() === $serviceIdString
                            && in_array(
                                $orderItemIdToSku[$product['item_id']] ?? '',
                                $splitShipmentsIdx[$orderPackage['origin_id']]['products'] ?? []
                            );
                    }
                );
                $orderPackage['products'] = $packageProducts;
            }
        }
        $orderPackages = array_filter($orderPackages, static function ($package) {
            return !empty($package['products']);
        });

        if ($orderPackages) {
            if ($order->getData(CustomSalesAttributesInterface::CARRIER_PACKAGES)) {
                $packagesSet = $this->serializer->unserialize(
                    $order->getData(CustomSalesAttributesInterface::CARRIER_PACKAGES)
                );
                $orderPackages = array_merge($packagesSet, $orderPackages);
            }
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

    /**
     * @param Order $order
     * @return array
     */
    private function getOrderItemIdToSkuMap(Order $order): array
    {
        $map = [];
        foreach ($order->getAllItems() as $orderItem) {
            $map[$orderItem->getId()] = $orderItem->getSku();
        }

        return $map;
    }
}
