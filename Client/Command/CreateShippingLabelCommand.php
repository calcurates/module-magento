<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client\Command;

use Calcurates\ModuleMagento\Api\Client\CalcuratesClientInterface;
use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Calcurates\ModuleMagento\Api\Data\ShippingLabelInterface;
use Calcurates\ModuleMagento\Api\Data\ShippingLabelInterfaceFactory;
use Calcurates\ModuleMagento\Api\ShippingLabelRepositoryInterface;
use Calcurates\ModuleMagento\Client\Request\ShippingLabelRequestBuilder;
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
use Calcurates\ModuleMagento\Model\Config;
use Calcurates\ModuleMagento\Observer\ShipmentSaveAfterObserver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class CreateShippingLabelCommand
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Framework\App\ScopeInterface|int|string|null
     */
    private $store;

    /**
     * @var ShippingLabelRequestBuilder
     */
    private $shippingLabelRequestBuilder;

    /**
     * @var CalcuratesClientInterface
     */
    private $calcuratesClient;

    /**
     * @var ShippingLabelInterfaceFactory
     */
    private $shippingLabelFactory;

    /**
     * @var ShippingLabelRepositoryInterface
     */
    private $shippingLabelRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ShippingMethodManager
     */
    private $shippingMethodManager;

    /**
     * CreateShippingLabelCommand constructor.
     * @param LoggerInterface $logger
     * @param Config $config
     * @param ShippingLabelRequestBuilder $shippingLabelRequestBuilder
     * @param CalcuratesClientInterface $calcuratesClient
     * @param ShippingLabelInterfaceFactory $shippingLabelFactory
     * @param ShippingLabelRepositoryInterface $shippingLabelRepository
     * @param SerializerInterface $serializer
     * @param ShippingMethodManager $shippingMethodManager
     */
    public function __construct(
        LoggerInterface $logger,
        Config $config,
        ShippingLabelRequestBuilder $shippingLabelRequestBuilder,
        CalcuratesClientInterface $calcuratesClient,
        ShippingLabelInterfaceFactory $shippingLabelFactory,
        ShippingLabelRepositoryInterface $shippingLabelRepository,
        SerializerInterface $serializer,
        ShippingMethodManager $shippingMethodManager
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->shippingLabelRequestBuilder = $shippingLabelRequestBuilder;
        $this->calcuratesClient = $calcuratesClient;
        $this->shippingLabelFactory = $shippingLabelFactory;
        $this->shippingLabelRepository = $shippingLabelRepository;
        $this->serializer = $serializer;
        $this->shippingMethodManager = $shippingMethodManager;
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string|null $store
     */
    public function setStore($store)
    {
        $this->store = $store;
    }

    /**
     * @return int|\Magento\Framework\App\ScopeInterface|string|null
     */
    private function getStore()
    {
        return $this->store;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     * @return array
     * @throws LocalizedException
     */
    public function execute(\Magento\Framework\DataObject $request)
    {
        /** @var \Magento\Shipping\Model\Shipment\Request $request */
        $this->prepareShipmentRequest($request);

        $apiRequestBody = $this->shippingLabelRequestBuilder->build(
            $request,
            $this->isDebug()
        );

        $debugData = [
            'request' => $apiRequestBody,
            'type' => 'shippingLabelCreate'
        ];

        try {
            $shippingLabelResponse = $this->calcuratesClient->createShippingLabel($apiRequestBody, $this->getStore());
            $debugData['result'] = $shippingLabelResponse;
        } catch (LocalizedException $e) {
            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            $this->debug($debugData);
            throw $e;
        }
        $this->debug($debugData);

        $labelContent = '';
        if (!empty($shippingLabelResponse['labelDownload'])) {
            $labelContent = $this->downloadLabelContent($shippingLabelResponse['labelDownload']);
        }

        $order = $request->getOrderShipment()->getOrder();
        /** @var ShippingLabelInterface $shippingLabel */
        $shippingLabel = $this->shippingLabelFactory->create();
        $shippingLabel->setLabelContent($labelContent);

        $shippingLabelDataSerialized = $this->serializer->serialize($shippingLabelResponse);
        $shippingLabel->setLabelData($shippingLabelDataSerialized);

        $serviceId = $apiRequestBody['service'];
        $shippingLabel->setShippingServiceId($serviceId);
        $carriersWithServices = $this->calcuratesClient->getShippingCarriersWithServices($this->getStore());
        $serviceFound = false;
        if ($carriersWithServices) {
            foreach ($carriersWithServices as $carrier) {
                foreach ($carrier['services'] as $service) {
                    if ($service['value'] == $serviceId) {
                        $shippingLabel->setShippingCarrierId((string)$carrier['id']);
                        $shippingLabel->setShippingCarrierLabel((string)$carrier['label']);
                        $shippingLabel->setShippingServiceLabel((string)$service['label']);
                        $serviceFound = true;
                        break;
                    }
                }
            }
        }

        if (!$serviceFound) {
            $carrierData = $this->shippingMethodManager->getCarrierData(
                $order->getShippingMethod(false),
                $order->getShippingDescription()
            );

            $shippingLabel->setShippingCarrierId((string)$carrierData->getCarrierId());
            $shippingLabel->setShippingCarrierLabel((string)$carrierData->getCarrierLabel());
            $shippingLabel->setShippingServiceLabel((string)$carrierData->getServiceLabel());
        }

        $trackingNumber = !empty($shippingLabelResponse['trackingNumber'])
            ? $shippingLabelResponse['trackingNumber'] : '';

        $shippingLabel->setTrackingNumber($trackingNumber);
        $shippingLabel->setPackages($request->getPackages());

        $request->getOrderShipment()->setData(ShipmentSaveAfterObserver::SHIPPING_LABEL_KEY, $shippingLabel);
        $request->getOrderShipment()->setData(CustomSalesAttributesInterface::LABEL_DATA, $shippingLabelDataSerialized);

        return [
            'tracking_number' => $trackingNumber,
            'label_content' => $labelContent
        ];
    }

    /**
     * @param string $url
     * @return string
     * @throws LocalizedException
     */
    private function downloadLabelContent($url)
    {
        $debugData = ['request' => $url, 'type' => 'shippingLabelDownload'];
        try {
            $result = $this->calcuratesClient->getLabelContent($url);
            $debugData['result'] = $result;
        } catch (LocalizedException $e) {
            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            $this->debug($debugData);
            throw $e;
        }
        $this->debug($debugData);

        return $result;
    }

    /**
     * @return bool
     */
    private function isDebug()
    {
        return $this->config->isDebug($this->getStore());
    }

    /**
     * Prepare shipment request. Validate and correct request information
     *
     * @param \Magento\Framework\DataObject $request
     * @return void
     */
    private function prepareShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $phonePattern = '/[\s\_\-\(\)]+/';
        $phoneNumber = $request->getShipperContactPhoneNumber();
        $phoneNumber = preg_replace($phonePattern, '', $phoneNumber);
        $request->setShipperContactPhoneNumber($phoneNumber);
        $phoneNumber = $request->getRecipientContactPhoneNumber();
        $phoneNumber = preg_replace($phonePattern, '', $phoneNumber);
        $request->setRecipientContactPhoneNumber($phoneNumber);
    }

    /**
     * Log debug data to file
     *
     * @TODO: move to debugger service
     *
     * @param mixed $debugData
     * @return void
     */
    protected function debug($debugData)
    {
        if ($this->isDebug()) {
            $this->logger->debug(var_export($debugData, true));
        }
    }

}
