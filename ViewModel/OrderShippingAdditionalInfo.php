<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\ViewModel;

use Calcurates\ModuleMagento\Api\Data\OrderDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\OrderData\GetOrderDataInterface;
use Calcurates\ModuleMagento\Api\ShippingLabelRepositoryInterface;
use Calcurates\ModuleMagento\Model\Shipment\CarrierPackagesRetriever;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Shipping\Helper\Data as ShippingHelper;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Tax\Helper\Data as TaxHelper;

class OrderShippingAdditionalInfo implements ArgumentInterface
{
    /**
     * @var OrderDataInterface
     */
    private $orderData;

    /**
     * @var OrderInterface
     */
    private $order;

    /**
     * @var GetOrderDataInterface
     */
    private $getOrderData;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var CarrierFactory
     */
    private $carrierFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CarrierPackagesRetriever
     */
    private $packagesRetriever;

    /**
     * @var TaxHelper
     */
    private $taxHelper;

    /**
     * @var ShippingHelper
     */
    private $shippingHelper;

    /**
     * @var ShippingLabelRepositoryInterface
     */
    private $labelRepository;

    /**
     * @param GetOrderDataInterface $getOrderData
     * @param PriceCurrencyInterface $priceCurrency
     * @param CarrierFactory $carrierFactory
     * @param Registry $registry
     * @param CarrierPackagesRetriever $packagesRetriever
     * @param TaxHelper $taxHelper
     * @param ShippingHelper $shippingHelper
     * @param ShippingLabelRepositoryInterface $labelRepository
     */
    public function __construct(
        GetOrderDataInterface $getOrderData,
        PriceCurrencyInterface $priceCurrency,
        CarrierFactory $carrierFactory,
        Registry $registry,
        CarrierPackagesRetriever $packagesRetriever,
        TaxHelper $taxHelper,
        ShippingHelper $shippingHelper,
        ShippingLabelRepositoryInterface $labelRepository
    ) {
        $this->getOrderData = $getOrderData;
        $this->priceCurrency = $priceCurrency;
        $this->carrierFactory = $carrierFactory;
        $this->registry = $registry;
        $this->packagesRetriever = $packagesRetriever;
        $this->taxHelper = $taxHelper;
        $this->shippingHelper = $shippingHelper;
        $this->labelRepository = $labelRepository;
    }

    /**
     * @return bool
     */
    public function isSplitShipment(): bool
    {
        return $this->order->getShippingMethod() === 'calcurates_metarate'
            && $this->orderData
            && $this->orderData->getSplitShipments();
    }

    /**
     * @param OrderInterface $order
     * @return void
     */
    public function setOrder(OrderInterface $order): void
    {
        $this->order = $order;
        $this->orderData = $this->getOrderData->get($order->getId());
    }

    /**
     * @return OrderInterface|null
     */
    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    /**
     * @return OrderDataInterface
     */
    public function getOrderData(): OrderDataInterface
    {
        return $this->orderData;
    }

    /**
     * @param float $price
     * @return string
     */
    public function getMethodPrice(float $price): string
    {
        return $this->priceCurrency->convertAndFormat(
            $price,
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getOrder()->getStoreId()
        );
    }

    /**
     * Get all packages from all shipments
     * @return array
     */
    public function getPackages(): array
    {
        $packages = [];
        foreach ($this->getOrder()->getShipmentsCollection()->getItems() as $shipment) {
            $this->registry->register('current_shipment', $shipment);
            foreach ($shipment->getPackages() as $package) {
                $package['params']['name'] = $this->getContainerTypeByCode($package['params']['container']);
                $packages[] = $package;
            }
            $this->registry->unregister('current_shipment');
        }
        if (empty($packages)) {
            $packages = $this->packagesRetriever->retrievePackages($this->getOrder());
        }
        return $packages;
    }

    /**
     * Get package type quantities
     * @return array
     */
    public function getPackagesQty(): array
    {
        $packagesQty = [];
        foreach ($this->getPackages() as $package) {
            $packageName = $package['params']['name'] ?? $package['name'];
            if (!isset($packagesQty[$packageName])) {
                $packagesQty[$packageName] = 1;
            } else {
                $packagesQty[$packageName] += 1;
            }
        }
        return $packagesQty;
    }

    /**
     * @param $code
     * @return string
     */
    public function getContainerTypeByCode($code): string
    {
        $carrier = $this->carrierFactory->create($this->getOrder()->getShippingMethod(true)->getCarrierCode());
        if ($carrier) {
            $containerTypes = $carrier->getContainerTypes();
            return !empty($containerTypes[$code]) ? $containerTypes[$code] : '';
        }
        return '';
    }

    /**
     * Check if all carrier services in shipment(s) cannot track orders
     * Get data from Calcurates labels data.
     * @param ShipmentInterface|null $shipment
     * @return bool
     */
    public function isTrackable(?ShipmentInterface $shipment = null): bool
    {
        $isTrackable = true;
        $untrackableCount = 0;
        $currentShipment = $shipment ?? $this->registry->registry('current_shipment');
        $shipments = $currentShipment ? [$currentShipment] : $this->getOrder()->getShipmentsCollection()->getItems();
        try {
            foreach ($shipments as $shipment) {
                /** @var ShipmentInterface $shipment */
                $labelData = $this->labelRepository->getLastByShipmentId((int)$shipment->getId())->getLabelData();
                if ($labelData['trackable'] === false) {
                    $untrackableCount++;
                }
            }
            $isTrackable = count($shipments) !== $untrackableCount;
        } catch (\Exception $e) {
        }
        return $isTrackable;
    }

    /**
     * @return TaxHelper
     */
    public function getTaxHelper(): TaxHelper
    {
        return $this->taxHelper;
    }

    /**
     * @return ShippingHelper
     */
    public function getShippingHelper(): ShippingHelper
    {
        return $this->shippingHelper;
    }
}
