<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client\Command;

use Calcurates\ModuleMagento\Api\Client\CalcuratesClientInterface;
use Calcurates\ModuleMagento\Api\Data\ShippingLabelInterface;
use Calcurates\ModuleMagento\Api\Data\ShippingLabelInterfaceFactory;
use Calcurates\ModuleMagento\Client\Request\ShippingLabelRequestBuilder;
use Calcurates\ModuleMagento\Model\Config;
use Calcurates\ModuleMagento\Observer\ShipmentSaveAfterObserver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
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
     * @var GetShippingOptionsCommand
     */
    private $getShippingOptionsCommand;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        LoggerInterface $logger,
        Config $config,
        ShippingLabelRequestBuilder $shippingLabelRequestBuilder,
        CalcuratesClientInterface $calcuratesClient,
        ShippingLabelInterfaceFactory $shippingLabelFactory,
        GetShippingOptionsCommand $getShippingOptionsCommand,
        StoreManagerInterface $storeManager
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->shippingLabelRequestBuilder = $shippingLabelRequestBuilder;
        $this->calcuratesClient = $calcuratesClient;
        $this->shippingLabelFactory = $shippingLabelFactory;
        $this->getShippingOptionsCommand = $getShippingOptionsCommand;
        $this->storeManager = $storeManager;
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

        $shippingServiceId = $request->getOrderShipment()->getData('calcuratesShippingServiceId');
        $shippingCarrierData = $this->getDataByShippingServiceId($shippingServiceId);
        if (!$shippingCarrierData) {
            throw new LocalizedException(__('Incorrect shipping service'));
        }

        $request->setData('calcurates_carrier_code', $shippingCarrierData['carrierType']);
        $request->setData('calcurates_provider_code', $shippingCarrierData['carrierProvider']);
        $request->setData('calcurates_service_code', $shippingCarrierData['service']['code']);

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
        } catch (\Exception $e) {
            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            throw $e;
        } finally {
            $this->debug($debugData);
        }

        $labelContent = '';
        if (!empty($shippingLabelResponse['labelDownload'])) {
            $labelContent = $this->downloadLabelContent($shippingLabelResponse['labelDownload'], $this->getStore());
        }

        /** @var ShippingLabelInterface $shippingLabel */
        $shippingLabel = $this->shippingLabelFactory->create();
        $shippingLabel->setLabelContent($labelContent);

        $shippingLabel->setLabelData($shippingLabelResponse);

        $shippingLabel->setShippingServiceId($shippingServiceId);
        $shippingLabel->setShippingCarrierId((string)$shippingCarrierData['id']);
        $shippingLabel->setShippingCarrierLabel((string)$shippingCarrierData['shippingOption']['name']);
        $shippingLabel->setShippingServiceLabel((string)$shippingCarrierData['service']['name']);
        $shippingLabel->setCarrierCode($shippingCarrierData['carrierType']);
        $shippingLabel->setCarrierProviderCode($shippingCarrierData['carrierProvider']);

        $trackingNumber = !empty($shippingLabelResponse['trackingNumber'])
            ? $shippingLabelResponse['trackingNumber'] : '';

        $shippingLabel->setTrackingNumber($trackingNumber);
        $shippingLabel->setPackages($request->getPackages());

        $request->getOrderShipment()->setData(ShipmentSaveAfterObserver::SHIPPING_LABEL_KEY, $shippingLabel);

        return [
            'tracking_number' => $trackingNumber,
            'label_content' => $labelContent
        ];
    }

    /**
     * @param int $shippingServiceId
     * @return array|null
     */
    private function getDataByShippingServiceId(int $shippingServiceId): ?array
    {
        $storeId = (int)$this->storeManager->getStore($this->getStore())->getId();
        $carriersWithServices = $this->getShippingOptionsCommand->get(
            $storeId,
            GetShippingOptionsCommand::TYPE_CARRIERS
        );

        $result = null;
        if ($carriersWithServices) {
            foreach ($carriersWithServices as $carrier) {
                foreach ($carrier['services'] as $service) {
                    if ($service['id'] === $shippingServiceId) {
                        $result = $carrier;
                        $result['service'] = $service;
                        unset($result['services']);
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param string $url
     * @param int $storeId
     * @return string
     * @throws LocalizedException
     */
    private function downloadLabelContent($url, $storeId)
    {
        $debugData = ['request' => $url, 'type' => 'shippingLabelDownload'];
        try {
            $result = $this->calcuratesClient->getLabelContent($url, $storeId);
            $debugData['result'] = $result;
        } catch (\Exception $e) {
            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            throw $e;
        } finally {
            $this->debug($debugData);
        }

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
