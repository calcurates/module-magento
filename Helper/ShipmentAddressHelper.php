<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Helper;

use Calcurates\ModuleMagento\Api\Client\CalcuratesClientInterface;
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
use Calcurates\ModuleMagento\Model\Source\ShipmentServiceRetriever;
use Calcurates\ModuleMagento\Model\Source\ShipmentSourceCodeRetriever;
use Calcurates\ModuleMagento\Model\Source\SourceAddressService;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Shipment;
use Magento\Store\Model\ScopeInterface;

class ShipmentAddressHelper extends AbstractHelper
{
    /**
     * @var Address\Renderer
     */
    private $addressRenderer;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * @var \Magento\Sales\Model\Order\AddressFactory
     */
    private $addressFactory;

    /**
     * @var CalcuratesClientInterface
     */
    private $calcuratesClient;

    /**
     * @var SourceAddressService
     */
    private $sourceAddressService;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    private $regionFactory;

    /**
     * @var ShipmentSourceCodeRetriever
     */
    private $shipmentSourceCodeRetriever;

    /**
     * @var ShipmentServiceRetriever
     */
    private $shipmentServiceRetriever;

    /**
     * @var ShippingMethodManager
     */
    private $shippingMethodManager;

    /**
     * ShipmentAddressHelper constructor.
     * @param Context $context
     * @param Address\Renderer $addressRenderer
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Sales\Model\Order\AddressFactory $addressFactory
     * @param CalcuratesClientInterface $calcuratesClient
     * @param SourceAddressService $sourceAddressService
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param ShipmentSourceCodeRetriever $shipmentSourceCodeRetriever
     * @param ShipmentServiceRetriever $shipmentServiceRetriever
     * @param ShippingMethodManager $shippingMethodManager
     */
    public function __construct(
        Context $context,
        Address\Renderer $addressRenderer,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Sales\Model\Order\AddressFactory $addressFactory,
        CalcuratesClientInterface $calcuratesClient,
        SourceAddressService $sourceAddressService,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        ShipmentSourceCodeRetriever $shipmentSourceCodeRetriever,
        ShipmentServiceRetriever $shipmentServiceRetriever,
        ShippingMethodManager $shippingMethodManager
    ) {
        parent::__construct($context);
        $this->addressRenderer = $addressRenderer;
        $this->authSession = $authSession;
        $this->addressFactory = $addressFactory;
        $this->calcuratesClient = $calcuratesClient;
        $this->sourceAddressService = $sourceAddressService;
        $this->regionFactory = $regionFactory;
        $this->shipmentSourceCodeRetriever = $shipmentSourceCodeRetriever;
        $this->shipmentServiceRetriever = $shipmentServiceRetriever;
        $this->shippingMethodManager = $shippingMethodManager;
    }

    /**
     * Returns string with formatted address
     *
     * @param Address $address
     * @return null|string
     */
    public function getFormattedAddress(Address $address)
    {
        return $this->addressRenderer->format($address, 'html');
    }

    /**
     * @param Shipment $orderShipment
     * @return string|null
     * @throws LocalizedException
     */
    public function getOriginAddressHtml(Shipment $orderShipment)
    {
        $addressData = $this->sourceAddressService->getAddressDataByShipment(
            $orderShipment
        );
        if (!$addressData) {
            $addressData = $this->getOriginAddressData($orderShipment);
        }

        /** @var Address $address */
        $address = $this->addressFactory->create(['data' => $addressData]);
        $address->setAddressType(Address::TYPE_SHIPPING);

        return $this->getFormattedAddress($address);
    }

    /**
     * @param Order $order
     * @param Shipment $shipment
     * @return array
     */
    public function getShippingCarriersWithServices(Order $order, $shipment)
    {
        $carrierData = $this->shippingMethodManager->getCarrierData(
            $order->getShippingMethod(),
            $order->getShippingDescription()
        );
        if (!$carrierData) {
            return [];
        }

        $shippingCarriersWithServices = $this->calcuratesClient->getShippingCarriersWithServices($order->getStoreId());

        if (empty($shippingCarriersWithServices)) {
            $shippingServiceValue = $this->getShippingServiceId($order, $shipment);
            $shippingCarriersWithServices = [
                [
                    'label' => __('Default'),
                    'services' => [
                        [
                            'value' => $shippingServiceValue,
                            'label' => $carrierData->getServiceLabel()
                        ]
                    ]
                ]
            ];
        }

        return $shippingCarriersWithServices;
    }

    /**
     * @param Order $order
     * @param Shipment $shipment
     * @return string
     */
    public function getShippingServiceId(Order $order, $shipment)
    {
        $sourceCode = $this->shipmentSourceCodeRetriever->retrieve($shipment);

        return  $this->shipmentServiceRetriever->retrieve($order, $sourceCode);
    }

    /**
     * @param Shipment $orderShipment
     * @return array|false
     */
    private function getOriginAddressData(Shipment $orderShipment)
    {
        $admin = $this->authSession->getUser();
        $storeInfo = new DataObject(
            (array)$this->scopeConfig->getValue(
                'general/store_information',
                ScopeInterface::SCOPE_STORE,
                $orderShipment->getStoreId()
            )
        );

        $originStreet = $this->scopeConfig->getValue(
            Shipment::XML_PATH_STORE_ADDRESS1,
            ScopeInterface::SCOPE_STORE,
            $orderShipment->getStoreId()
        );

        $originStreet2 = $this->scopeConfig->getValue(
            Shipment::XML_PATH_STORE_ADDRESS2,
            ScopeInterface::SCOPE_STORE,
            $orderShipment->getStoreId()
        );

        $city = $this->scopeConfig->getValue(
            Shipment::XML_PATH_STORE_CITY,
            ScopeInterface::SCOPE_STORE,
            $orderShipment->getStoreId()
        );
        $postcode = $this->scopeConfig->getValue(
            Shipment::XML_PATH_STORE_ZIP,
            ScopeInterface::SCOPE_STORE,
            $orderShipment->getStoreId()
        );

        $shipperRegionCode = $this->scopeConfig->getValue(
            Shipment::XML_PATH_STORE_REGION_ID,
            ScopeInterface::SCOPE_STORE,
            $orderShipment->getStoreId()
        );
        if (is_numeric($shipperRegionCode)) {
            $shipperRegionCode = $this->regionFactory->create()->load($shipperRegionCode)->getCode();
        }

        $countryId = $this->scopeConfig->getValue(
            Shipment::XML_PATH_STORE_COUNTRY_ID,
            ScopeInterface::SCOPE_STORE,
            $orderShipment->getStoreId()
        );

        return [
            'firstname' => $admin->getFirstName(),
            'lastname' => $admin->getLastName(),
            'company' => $storeInfo->getName(),
            'street' => trim($originStreet . ' ' . $originStreet2),
            'city' => $city,
            'postcode' => $postcode,
            'region' => $shipperRegionCode,
            'country_id' => $countryId,
            'email' => $admin->getEmail(),
            'telephone' => $storeInfo->getPhone()
        ];
    }
}
