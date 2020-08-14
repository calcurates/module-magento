<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model;

use Calcurates\ModuleMagento\Api\Client\CalcuratesClientInterface;
use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Calcurates\ModuleMagento\Client\Command\CreateShippingLabelCommand;
use Calcurates\ModuleMagento\Client\Request\RateRequestBuilder;
use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Calcurates\ModuleMagento\Client\Request\ShippingLabelRequestBuilder;
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
use Calcurates\ModuleMagento\Model\Carrier\Validator\RateRequestValidator;
use Calcurates\ModuleMagento\Model\Shipment\CustomPackagesProvider;
use Calcurates\ModuleMagento\Model\Shipment\ShippingLabelSaver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Carrier extends AbstractCarrierOnline implements CarrierInterface
{
    /**
     * Code of the carrier
     *
     * @var string
     */
    const CODE = 'calcurates';

    /**
     * Code of the carrier
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * @var Result
     */
    protected $result;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Shipping\Model\Order\Track
     */
    protected $trackingObject;

    /**
     * @var CalcuratesClientInterface
     */
    private $calcuratesClient;

    /**
     * @var RatesResponseProcessor
     */
    private $ratesResponseProcessor;

    /**
     * @var RateRequestBuilder
     */
    private $rateRequestBuilder;

    /**
     * @var RateRequestValidator
     */
    private $rateRequestValidator;

    /**
     * @var ShippingMethodManager
     */
    private $shippingMethodManager;

    /**
     * @var CustomPackagesProvider
     */
    private $customPackagesProvider;

    /**
     * @var CreateShippingLabelCommand
     */
    private $createShippingLabelCommand;

    /**
     * Carrier constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Framework\Registry $registry
     * @param CalcuratesClientInterface $calcuratesClient
     * @param RatesResponseProcessor $ratesResponseProcessor
     * @param RateRequestBuilder $rateRequestBuilder
     * @param ShippingLabelRequestBuilder $shippingLabelRequestBuilder
     * @param RateRequestValidator $rateRequestValidator
     * @param ShippingLabelSaver $shippingLabelSaver
     * @param ShippingMethodManager $shippingMethodManager
     * @param CustomPackagesProvider $customPackagesProvider
     * @param CreateShippingLabelCommand $createShippingLabelCommand
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Registry $registry,
        CalcuratesClientInterface $calcuratesClient,
        RatesResponseProcessor $ratesResponseProcessor,
        RateRequestBuilder $rateRequestBuilder,
        RateRequestValidator $rateRequestValidator,
        ShippingMethodManager $shippingMethodManager,
        CustomPackagesProvider $customPackagesProvider,
        CreateShippingLabelCommand $createShippingLabelCommand,
        array $data = []
    ) {
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
        $this->registry = $registry;
        $this->calcuratesClient = $calcuratesClient;
        $this->ratesResponseProcessor = $ratesResponseProcessor;
        $this->rateRequestBuilder = $rateRequestBuilder;
        $this->rateRequestValidator = $rateRequestValidator;
        $this->shippingMethodManager = $shippingMethodManager;
        $this->customPackagesProvider = $customPackagesProvider;
        $this->createShippingLabelCommand = $createShippingLabelCommand;
    }

    /**
     * Collect and get rates/errors
     *
     * @param RateRequest $request
     *
     * @return  Result|Error|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag(Config::ACTIVE) || $request->getSkipCalcurates()) {
            return false;
        }

        if ($this->rateRequestValidator->validate($request)) {
            $result = $this->getQuotes($request);
            if ($result === false) {
                return false;
            }
            $this->result = $result;
        } else {
            $result = $this->_rateFactory->create();
            $this->ratesResponseProcessor->processFailedRate(
                (string)$this->getConfigData('title'),
                $result,
                (string)__('Please fill in the required delivery address fields to get shipping quotes')
            );
            $this->result = $result;
        }
        $this->_updateFreeMethodQuote($request);

        return $this->getResult();
    }

    /**
     * Get result of request
     *
     * @return Result
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param RateRequest $request
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @see \Magento\Shipping\Model\Carrier\AbstractCarrierOnline::getAllItems
     */
    public function getAllItems(RateRequest $request)
    {
        $items = [];

        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                /* @var $item Item */
                if ($item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if (!$child->getFreeShipping()) {
                            $items[] = $child;
                        }
                    }
                } else {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    /**
     * @param RateRequest $request
     *
     * @return Result|bool
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getQuotes(RateRequest $request)
    {
        $items = $this->getAllItems($request);
        if (!count($items)) {
            return false;
        }
        $quote = current($items)->getQuote();
        $apiRequestBody = $this->rateRequestBuilder->build(
            $request,
            $items
        );

        $debugData['request'] = $apiRequestBody;

        try {
            $response = $this->calcuratesClient->getRates($apiRequestBody, $this->getStore());
            $debugData['result'] = $response;
        } catch (LocalizedException $e) {
            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            $response = [];
        }
        $this->_debug($debugData);

        return $this->ratesResponseProcessor->processResponse($response, $quote);
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [];
    }

    /**
     * Processing additional validation to check is carrier applicable.
     *
     * @param DataObject $request
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function processAdditionalValidation(DataObject $request)
    {
        return $this;
    }

    /**
     * Do request to shipment
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return \Magento\Framework\DataObject
     * @throws LocalizedException
     */
    public function requestToShipment($request)
    {
        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
            throw new LocalizedException(__('No packages for request'));
        }
        $result = $this->_doShipmentRequest($request);
        $request->setMasterTrackingId($result['tracking_number']);

        return new \Magento\Framework\DataObject(
            [
                'info' => [$result],
            ]
        );
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Framework\DataObject $request
     * @return array
     * @throws LocalizedException
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $this->createShippingLabelCommand->setStore($this->getStore());

        return $this->createShippingLabelCommand->execute($request);
    }

    /**
     * Check if city option required
     *
     * @return bool
     */
    public function isCityRequired()
    {
        return false;
    }

    /**
     * Determine whether zip-code is required for the country of destination
     *
     * @param string|null $countryId
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isZipCodeRequired($countryId = null)
    {
        return false;
    }

    /**
     * Check if carrier has shipping label option available
     *
     * @return bool
     */
    public function isShippingLabelsAvailable()
    {
        /** @var \Magento\Sales\Model\Order\Shipment|null $shipment */
        $shipment = $this->registry->registry('current_shipment');
        if (!$shipment) {
            return false;
        }
        $method = $shipment->getOrder()->getShippingMethod(true);
        return strpos($method->getMethod(), ShippingMethodManager::CARRIER) === 0;
    }

    /**
     * Get tracking information
     *
     * @param string $tracking
     * @param \Magento\Shipping\Model\Order\Track|null $trackObject
     * @return string|false
     * @throws LocalizedException
     * @api
     */
    public function getTrackingInfo($tracking, $trackObject = null)
    {
        $this->trackingObject = $trackObject;
        return parent::getTrackingInfo($tracking);
    }

    /**
     * Get tracking
     *
     * @param @param string|string[] $trackings
     * @return \Magento\Shipping\Model\Tracking\Result|null
     * @throws LocalizedException
     */
    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        $result = $this->_trackFactory->create();

        if (!empty($this->trackingObject)) {
            foreach ($trackings as $tracking) {
                $this->loadTracking($tracking, $result);
            }
        }

        return $result;
    }

    /**
     * @param string $tracking
     * @param \Magento\Shipping\Model\Tracking\Result $result
     * @throws LocalizedException
     */
    protected function loadTracking($tracking, $result)
    {
        $serviceId = $this->trackingObject->getData(CustomSalesAttributesInterface::SERVICE_ID);
        if (empty($serviceId)) {
            // backward compatibility
            $order = $this->trackingObject->getShipment()->getOrder();
            $carrierData = $this->shippingMethodManager->getCarrierData(
                $order->getShippingMethod(false),
                $order->getShippingDescription()
            );

            if ($carrierData) {
                $serviceId = $carrierData->getServiceIdsString();
            }
        }

        $debugData = ['request' => $serviceId . ' - ' . $tracking, 'type' => 'tracking'];
        $response = [];
        try {
            $response = $this->calcuratesClient->getTrackingInfo($serviceId, $tracking, $this->getStore());
            $debugData['result'] = $response;
        } catch (\Throwable $e) {
            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
        }
        $this->_debug($debugData);
        $this->parseTrackingData($response, $result);
    }

    /**
     * @param array $response
     * @param \Magento\Shipping\Model\Tracking\Result $result
     */
    protected function parseTrackingData(array $response, $result)
    {
        $carrierTitle = $this->trackingObject->getTitle();
        if (!empty($response['trackingNumber'])) {
            $tracking = $this->_trackStatusFactory->create();
            $tracking->setCarrier(self::CODE);
            $tracking->setCarrierTitle($carrierTitle);
            $tracking->setTracking($response['trackingNumber']);
            $tracking->addData($this->processTrackingDetails($response));
            $result->append($tracking);
        } else {
            $error = $this->_trackErrorFactory->create();
            $error->setCarrier(self::CODE);
            $error->setCarrierTitle($carrierTitle);
            $error->setTracking($this->trackingObject->getTrackNumber());
            $error->setErrorMessage(!empty($response['message']) ? $response['message'] : __('Tracking getting error'));
            $result->append($error);
        }
    }

    /**
     * @param array $response
     * @return array
     */
    protected function processTrackingDetails($response)
    {
        $result = [
            'shippedDate' => null,
            'deliverydate' => null,
            'deliverytime' => null,
            'deliverylocation' => null,
            'weight' => null,
            'progressdetail' => [],
        ];
        $datetime = $this->parseDate(!empty($response['shipDate']) ? $response['shipDate'] : null);
        if ($datetime) {
            $result['shippedDate'] = gmdate('Y-m-d', $datetime->getTimestamp());
        }

        $field = 'estimatedDeliveryDate';
        // if delivered - get actual date
        if (!empty($response['statusCode']) && $response['statusCode'] === 'DE') {
            $field = 'actualDeliveryDate';
        }
        $datetime = $this->parseDate(!empty($response[$field]) ? $response[$field] : null);
        if ($datetime) {
            $result['deliverydate'] = gmdate('Y-m-d', $datetime->getTimestamp());
            $result['deliverytime'] = gmdate('H:i:s', $datetime->getTimestamp());
        }

        if (!empty($response['events']) && is_array($response['events'])) {
            foreach ($response['events'] as $event) {
                $item = [
                    'activity' => !empty($event['description']) ? (string)$event['description'] : '',
                    'deliverydate' => null,
                    'deliverytime' => null,
                    'deliverylocation' => null
                ];
                $datetime = $this->parseDate(!empty($event['occurredAt']) ? $event['occurredAt'] : null);
                if ($datetime) {
                    $item['deliverydate'] = gmdate('Y-m-d', $datetime->getTimestamp());
                    $item['deliverytime'] = gmdate('H:i:s', $datetime->getTimestamp());
                }

                $result['progressdetail'][] = $item;
            }
        }

        return $result;
    }

    /**
     * Parses datetime string
     *
     * @param string $timestamp
     * @return bool|\DateTime
     */
    private function parseDate($timestamp)
    {
        if ($timestamp === null) {
            return false;
        }
        return \DateTime::createFromFormat(\DateTime::RFC3339, $timestamp);
    }

    /**
     * @param DataObject $params
     * @return array
     */
    public function getContainerTypes(\Magento\Framework\DataObject $params = null)
    {
        $customPackages = $this->customPackagesProvider->getCustomPackages($this->getStore());
        if ($customPackages) {
            $containerTypes = [];
            foreach ($customPackages as $customPackage) {
                $containerTypes[$customPackage['id']] = $customPackage['name'];
            }
        } else {
            $containerTypes = ['CUSTOM_PACKAGE' => __('Custom Package')];
        }

        return $containerTypes;
    }
}
