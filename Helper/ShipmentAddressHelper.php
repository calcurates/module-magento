<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Helper;

use Calcurates\ModuleMagento\Gateway\CarriersServicesOptionSource;
use Calcurates\ModuleMagento\Model\Source\ShipmentServiceRetriever;
use Calcurates\ModuleMagento\Model\Source\ShipmentSourceCodeRetriever;
use Calcurates\ModuleMagento\Model\Source\SourceAddressService;
use Magento\Backend\Model\Auth\Session;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\AddressFactory;
use Magento\Sales\Model\Order\Shipment;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class ShipmentAddressHelper extends AbstractHelper
{
    /**
     * @var Address\Renderer
     */
    private $addressRenderer;

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var SourceAddressService
     */
    private $sourceAddressService;

    /**
     * @var RegionFactory
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
     * @var CarriersServicesOptionSource
     */
    private $carriersServicesOptionSource;

    /**
     * @var CollectionFactory
     */
    private $countryCollectionFactory;

    /**
     * @param Context $context
     * @param Renderer $addressRenderer
     * @param Session $authSession
     * @param AddressFactory $addressFactory
     * @param SourceAddressService $sourceAddressService
     * @param RegionFactory $regionFactory
     * @param ShipmentSourceCodeRetriever $shipmentSourceCodeRetriever
     * @param ShipmentServiceRetriever $shipmentServiceRetriever
     * @param CarriersServicesOptionSource $carriersServicesOptionSource
     * @param CollectionFactory $countryCollectionFactory
     */
    public function __construct(
        Context $context,
        Address\Renderer $addressRenderer,
        Session $authSession,
        AddressFactory $addressFactory,
        SourceAddressService $sourceAddressService,
        RegionFactory $regionFactory,
        ShipmentSourceCodeRetriever $shipmentSourceCodeRetriever,
        ShipmentServiceRetriever $shipmentServiceRetriever,
        CarriersServicesOptionSource $carriersServicesOptionSource,
        CollectionFactory $countryCollectionFactory
    ) {
        parent::__construct($context);
        $this->addressRenderer = $addressRenderer;
        $this->authSession = $authSession;
        $this->addressFactory = $addressFactory;
        $this->sourceAddressService = $sourceAddressService;
        $this->regionFactory = $regionFactory;
        $this->shipmentSourceCodeRetriever = $shipmentSourceCodeRetriever;
        $this->shipmentServiceRetriever = $shipmentServiceRetriever;
        $this->carriersServicesOptionSource = $carriersServicesOptionSource;
        $this->countryCollectionFactory = $countryCollectionFactory;
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
     * @return Address
     */
    public function getOriginAddress(Shipment $orderShipment)
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

        return $address;
    }

    /**
     * @param Shipment $orderShipment
     * @return string|null
     * @throws LocalizedException
     */
    public function getOriginAddressHtml(Shipment $orderShipment)
    {
        return $this->getFormattedAddress($this->getOriginAddress($orderShipment));
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getShippingCarriersWithServices(int $storeId): array
    {
        return $this->carriersServicesOptionSource->getOptions($storeId);
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
     * @param null|int|string|Store $store
     * @return array
     */
    public function getCountriesOptions($store = null)
    {
        return $this->countryCollectionFactory->create()->loadByStore($store)->toOptionArray();
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
