<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Helper;

use Calcurates\ModuleMagento\Client\CalcuratesClient;
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
     * @var CalcuratesClient
     */
    private $calcuratesClient;

    /**
     * ShipmentAddressHelper constructor.
     * @param Context $context
     * @param Address\Renderer $addressRenderer
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Sales\Model\Order\AddressFactory $addressFactory
     * @param CalcuratesClient $calcuratesClient
     */
    public function __construct(
        Context $context,
        Address\Renderer $addressRenderer,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Sales\Model\Order\AddressFactory $addressFactory,
        CalcuratesClient $calcuratesClient
    ) {
        parent::__construct($context);
        $this->addressRenderer = $addressRenderer;
        $this->authSession = $authSession;
        $this->addressFactory = $addressFactory;
        $this->calcuratesClient = $calcuratesClient;
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
        $admin = $this->authSession->getUser();
        $originAddressFromCalcurates = $this->getOriginAddress($orderShipment);

        if (!$originAddressFromCalcurates) {
            return '';
        }
        $storeInfo = new DataObject(
            (array)$this->scopeConfig->getValue(
                'general/store_information',
                ScopeInterface::SCOPE_STORE,
                $orderShipment->getStoreId()
            )
        );
        $addressData = [
            'firstname' => $admin->getFirstName(),
            'lastname' => $admin->getLastName(),
            'company' => $storeInfo->getName(),
            'street' => trim($originAddressFromCalcurates['addressLine1'] . ' ' .
                $originAddressFromCalcurates['addressLine2']),
            'city' => $originAddressFromCalcurates['city'],
            'postcode' => $originAddressFromCalcurates['postalCode'],
            'region' => $originAddressFromCalcurates['regionName'],
            'country_id' => $originAddressFromCalcurates['country'],
            'email' => $admin->getEmail(),
            'telephone' => $originAddressFromCalcurates['contactPhone'],
        ];

        /** @var Address $address */
        $address = $this->addressFactory->create(['data' => $addressData]);
        $address->setAddressType(Address::TYPE_SHIPPING);

        return $this->getFormattedAddress($address);
    }

    /**
     * @param Order $order
     * @return array
     */
    public function getShippingServices(Order $order)
    {
        $method = $order->getShippingMethod(true)->getMethod();
        $methodId = current(explode('_', str_replace('carrier_', '', $method)));
        $shippingServices = $this->calcuratesClient->getShippingServices($methodId, $order->getStoreId());

        if (empty($shippingServices)) {
            $shippingServiceLabel = explode('-', $order->getShippingDescription());
            $shippingServiceLabel = end($shippingServiceLabel);
            $shippingServiceValue = $this->getShippingServiceId($order);
            $shippingServices[] = [
                'value' => $shippingServiceValue,
                'label' => $shippingServiceLabel
            ];
        }

        return $shippingServices;
    }

    /**
     * @param Order $order
     * @return mixed
     */
    public function getShippingServiceId(Order $order)
    {
        $method = explode('_', $order->getShippingMethod(true)->getMethod());
        return end($method);
    }

    /**
     * @param Shipment $orderShipment
     * @return array|false
     */
    private function getOriginAddress(Shipment $orderShipment)
    {
        $originData = $orderShipment->getOrder()->getData('calcurates_origin_data');
        if (!$originData || !is_string($originData)) {
            return false;
        }
        $originData = json_decode($originData, true);

        return $originData;
    }
}
