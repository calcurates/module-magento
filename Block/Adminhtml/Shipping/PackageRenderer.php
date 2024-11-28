<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Block\Adminhtml\Shipping;

use Calcurates\ModuleMagento\Api\Data\ShippingLabelInterface;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Shipping\Helper\Carrier;
use Magento\Shipping\Model\CarrierFactory;

class PackageRenderer extends Template
{
    /**
     * @var ShippingLabelInterface
     */
    private $shippingLabel;

    /**
     * @var ShipmentInterface
     */
    private $shipment;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var CarrierFactory
     */
    private $carrierFactory;

    /**
     * @var Carrier
     */
    private $carrierHelper;

    /**
     * PackageRenderer constructor.
     * @param Context $context
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param CarrierFactory $carrierFactory
     * @param Carrier $carrierHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        ShipmentRepositoryInterface $shipmentRepository,
        CarrierFactory $carrierFactory,
        Carrier $carrierHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->shipmentRepository = $shipmentRepository;
        $this->carrierFactory = $carrierFactory;
        $this->carrierHelper = $carrierHelper;
    }

    /**
     * @param ShippingLabelInterface $shippingLabel
     * @return string
     */
    public function render(ShippingLabelInterface $shippingLabel)
    {
        $this->shippingLabel = $shippingLabel;
        $this->shipment = $this->shipmentRepository->get($shippingLabel->getShipmentId());

        return $this->toHtml();
    }

    /**
     * @return ShippingLabelInterface
     */
    public function getShippingLabel()
    {
        return $this->shippingLabel;
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKeyInfo = parent::getCacheKeyInfo();
        $cacheKeyInfo[] = 'SHIPPING_LABEL_' . $this->getShippingLabel()->getId();

        return $cacheKeyInfo;
    }

    /**
     * @return array
     */
    public function getPackages(): array
    {
        return $this->getShippingLabel()->getPackages();
    }

    /**
     * @return ShipmentInterface
     */
    public function getShipment()
    {
        return $this->shipment;
    }

    /**
     * Return name of container type by its code
     *
     * @param string $code
     * @return string
     */
    public function getContainerTypeByCode($code)
    {
        $order = $this->getShipment()->getOrder();
        $carrier = $this->carrierFactory->create($order->getShippingMethod(true)->getCarrierCode());
        if ($carrier) {
            $containerTypes = $carrier->getContainerTypes();
            $containerType = !empty($containerTypes[$code]) ? $containerTypes[$code] : '';
            return $containerType;
        }
        return '';
    }

    /**
     * Can display customs value
     *
     * @return bool
     */
    public function displayCustomsValue(): bool
    {
        $storeId = $this->getShipment()->getStoreId();
        $order = $this->getShipment()->getOrder();
        $address = $order->getShippingAddress();
        $shipperAddressCountryCode = $this->_scopeConfig->getValue(
            \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $recipientAddressCountryCode = $address->getCountryId();
        if ($shipperAddressCountryCode != $recipientAddressCountryCode) {
            return true;
        }

        return false;
    }

    /**
     * Display formatted customs price
     *
     * @param float $price
     * @return string
     */
    public function displayCustomsPrice($price)
    {
        $orderInfo = $this->getShipment()->getOrder();
        return $orderInfo->getBaseCurrency()->formatTxt($price);
    }

    /**
     * @param string $weightUnits
     * @return string
     */
    public function getMeasureWeightName($weightUnits)
    {
        return $this->carrierHelper->getMeasureWeightName($weightUnits);
    }

    /**
     * @param string $dimensionUnits
     * @return string
     */
    public function getMeasureDimensionName($dimensionUnits)
    {
        return $this->carrierHelper->getMeasureDimensionName($dimensionUnits);
    }

    /**
     * Return name of delivery confirmation type by its code
     *
     * @param string $code
     * @return string
     */
    public function getDeliveryConfirmationTypeByCode($code)
    {
        $countryId = $this->getShipment()->getOrder()->getShippingAddress()->getCountryId();
        $order = $this->getShipment()->getOrder();
        $carrier = $this->carrierFactory->create($order->getShippingMethod(true)->getCarrierCode());
        if ($carrier) {
            $params = new \Magento\Framework\DataObject(['country_recipient' => $countryId]);
            $confirmationTypes = $carrier->getDeliveryConfirmationTypes($params);
            $confirmationType = !empty($confirmationTypes[$code]) ? $confirmationTypes[$code] : '';

            return $confirmationType;
        }

        return '';
    }

    /**
     * Return name of content type by its code
     *
     * @param string $code
     * @return string
     */
    public function getContentTypeByCode($code)
    {
        $contentTypes = $this->getContentTypes();
        if (!empty($contentTypes[$code])) {
            return $contentTypes[$code];
        }

        return '';
    }

    /**
     * Return content types of package
     *
     * @return array
     */
    public function getContentTypes()
    {
        $order = $this->getShipment()->getOrder();
        $storeId = $this->getShipment()->getStoreId();
        $address = $order->getShippingAddress();
        $carrier = $this->carrierFactory->create($order->getShippingMethod(true)->getCarrierCode());
        $countryShipper = $this->_scopeConfig->getValue(
            \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($carrier) {
            $params = new \Magento\Framework\DataObject(
                [
                    'method' => $order->getShippingMethod(true)->getMethod(),
                    'country_shipper' => $countryShipper,
                    'country_recipient' => $address->getCountryId(),
                ]
            );

            return $carrier->getContentTypes($params);
        }

        return [];
    }

    /**
     * Get ordered qty of item
     *
     * @param int $itemId
     * @return int|null
     */
    public function getQtyOrderedItem($itemId)
    {
        if ($itemId) {
            return $this->getShipment()->getOrder()->getItemById($itemId)->getQtyOrdered() * 1;
        }

        return null;
    }

    /**
     * Print button for creating pdf
     *
     * @return string
     */
    public function getPrintButton()
    {
        $data = [
            'shipment_id' => $this->getShipment()->getId(),
            'shipping_label_id' => $this->getShippingLabel()->getId()
        ];

        return $this->getUrl('adminhtml/order_shipment/printCalcuratesPackage', $data);
    }
}
