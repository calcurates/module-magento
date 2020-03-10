<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */
namespace Calcurates\ModuleMagento\Model;

use Calcurates\ModuleMagento\Client\CalcuratesClient;
use Calcurates\ModuleMagento\Client\RateRequestBuilder;
use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
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
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var CalcuratesClient
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
     * @param \Magento\Framework\App\RequestInterface $request
     * @param CalcuratesClient $calcuratesClient
     * @param RatesResponseProcessor $ratesResponseProcessor
     * @param RateRequestBuilder $rateRequestBuilder
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
        \Magento\Framework\App\RequestInterface $request,
        CalcuratesClient $calcuratesClient,
        RatesResponseProcessor $ratesResponseProcessor,
        RateRequestBuilder $rateRequestBuilder,
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
        $this->request = $request;
        $this->calcuratesClient = $calcuratesClient;
        $this->ratesResponseProcessor = $ratesResponseProcessor;
        $this->rateRequestBuilder = $rateRequestBuilder;
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
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if ($this->validateRequest($request)) {
            $this->result = $this->getQuotes($request);
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
                if ($item->getParentItem() || $item->getProduct()->isVirtual()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if (!$child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
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
     * @return Result
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getQuotes(RateRequest $request)
    {
        $items = $this->getAllItems($request);
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
        /** @var \Magento\Shipping\Model\Shipment\Request $request */
        $this->_prepareShipmentRequest($request);

        $shippingMethod = $this->request->getParam('calcuratesShippingServiceId');
        if (!$shippingMethod) {
            $shippingMethod = explode('_', $request->getShippingMethod());
            $shippingMethod = end($shippingMethod);
        }

        $apiRequestBody = [
            'service' => $shippingMethod,
            'origin' => $this->getOriginId($request),
            'shipTo' => [
                'name' => $request->getRecipientContactPersonName(),
                'phone' => $request->getRecipientContactPhoneNumber(),
                'companyName' => $request->getRecipientContactCompanyName(),
                'addressLine1' => $request->getRecipientAddressStreet1(),
                'addressLine2' => $request->getRecipientAddressStreet2(),
                'city' => $request->getRecipientAddressCity(),
                'region' => $request->getRecipientAddressStateOrProvinceCode(),
                'postalCode' => $request->getRecipientAddressPostalCode(),
                'country' => $request->getRecipientAddressCountryCode(),
                'addressResidentialIndicator' => 'unknown',
            ],
            'packages' => [],
            'testLabel' => (bool)$this->getDebugFlag(),
            'validateAddress' => 'no_validation',
        ];

        foreach ($request->getPackages() as $package) {
            $rawPackage = [
                'weight' => [
                    'value' => $package['params']['weight'],
                    'unit' => $this->getWeightUnits($package['params']['weight_units']),
                ],
                'dimensions' => [
                    'length' => $package['params']['length'],
                    'width' => $package['params']['width'],
                    'height' => $package['params']['height'],
                    'unit' => $this->getDimensionUnits($package['params']['dimension_units']),
                ],
            ];
            $apiRequestBody['packages'][] = $rawPackage;
        }

        $debugData = [
            'request' => $apiRequestBody,
            'type' => 'shippingLabelCreate'
        ];

        try {
            $response = $this->calcuratesClient->createShippingLabel($apiRequestBody, $this->getStore());
            $debugData['result'] = $response;
        } catch (LocalizedException $e) {
            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            $this->_debug($debugData);
            throw $e;
        }
        $this->_debug($debugData);

        return $this->prepareShippingLabelContent($response);
    }

    /**
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return string
     */
    protected function getOriginId($request)
    {
        $originData = $request->getOrderShipment()->getOrder()->getData('calcurates_origin_data');
        if (!$originData || !is_string($originData)) {
            return '';
        }
        $originData = json_decode($originData, true);

        return $originData['id'];
    }

    /**
     * @param string $weightUnits
     * @return string
     */
    protected function getWeightUnits($weightUnits)
    {
        switch ($weightUnits) {
            case \Zend_Measure_Weight::POUND:
                $weightUnits = 'pound';
                break;
            case \Zend_Measure_Weight::KILOGRAM:
                $weightUnits = 'kilogram';
                break;
            case \Zend_Measure_Weight::OUNCE:
                $weightUnits = 'ounce';
                break;
            case \Zend_Measure_Weight::GRAM:
                $weightUnits = 'gram';
                break;
            default:
                throw new \InvalidArgumentException('Invalid weight units');
        }

        return $weightUnits;
    }

    /**
     * @param string $dimensionUnits
     * @return string
     */
    protected function getDimensionUnits($dimensionUnits)
    {
        switch ($dimensionUnits) {
            case \Zend_Measure_Length::INCH:
                $dimensionUnits = 'inch';
                break;
            case \Zend_Measure_Length::CENTIMETER:
                $dimensionUnits = 'centimeter';
                break;
            default:
                throw new \InvalidArgumentException('Invalid dimension units');
        }

        return $dimensionUnits;
    }

    /**
     * @param array $labelData
     * @return array
     * @throws LocalizedException
     */
    protected function prepareShippingLabelContent(array $labelData)
    {
        $labelContent = '';
        if (!empty($labelData['labelDownload'])) {
            $labelContent = $this->downloadLabelContent($labelData['labelDownload']);
        }
        return [
            'tracking_number' => !empty($labelData['trackingNumber']) ? $labelData['trackingNumber'] : '',
            'label_content' => $labelContent
        ];
    }

    /**
     * @param string $url
     * @return string
     * @throws LocalizedException
     */
    protected function downloadLabelContent($url)
    {
        $debugData = ['request' => $url, 'type' => 'shippingLabelDownload'];
        try {
            $result = $this->calcuratesClient->getLabelContent($url);
            $debugData['result'] = $result;
        } catch (LocalizedException $e) {
            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            $this->_debug($debugData);
            throw $e;
        }
        $this->_debug($debugData);

        return $result;
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
        return strpos($method->getMethod(), 'carrier_') === 0;
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
        $shippingMethod = $this->trackingObject->getShipment()->getOrder()->getShippingMethod(false);
        $shippingMethod = explode('_', $shippingMethod);
        $serviceId = end($shippingMethod);
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
        return ['CUSTOM_PACKAGE' => __('Custom Package')];
    }

    /**
     * @param RateRequest $request
     * @return bool
     */
    private function validateRequest(RateRequest $request)
    {
        return !empty($request->getDestStreet())
            && !empty($request->getDestCity())
            && !empty($request->getDestRegionCode())
            && !empty($request->getDestPostcode())
            && !empty($request->getDestCountryId());

    }
}
