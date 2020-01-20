<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model;

use Calcurates\ModuleMagento\Client\CalcuratesClient;
use Calcurates\ModuleMagento\Model\Config as CalcuratesConfig;
use Magento\Directory\Model\ResourceModel\Region as RegionResource;
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
     * @var array
     */
    protected $regionNamesCache = [];

    /**
     * @var \Calcurates\ModuleMagento\Model\Config
     */
    protected $calcuratesConfig;

    /**
     * @var RegionResource
     */
    protected $regionResource;

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
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

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
     * @param Config $calcuratesConfig
     * @param RegionResource $regionResource
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param CalcuratesClient $calcuratesClient
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
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
        CalcuratesConfig $calcuratesConfig,
        RegionResource $regionResource,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        CalcuratesClient $calcuratesClient,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
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

        $this->calcuratesConfig = $calcuratesConfig;
        $this->regionResource = $regionResource;
        $this->registry = $registry;
        $this->request = $request;
        $this->calcuratesClient = $calcuratesClient;
        $this->priceCurrency = $priceCurrency;
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

        $this->result = $this->getQuotes($request);
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
        $streetArray = explode("\n", $request->getDestStreet());

        $apiRequestBody = [
            'country' => $request->getDestCountryId(),
            'regionCode' => $request->getDestRegionId() ? $request->getDestRegionCode() : null,
            'regionName' => $this->getRegionCodeById($request->getDestRegionId()) ?: $request->getDestRegionCode(),
            'postalCode' => $request->getDestPostcode(),
            'city' => $request->getDestCity(),
            'addressLine1' => $streetArray[0],
            'addressLine2' => $streetArray[1] ?? '',
            'customerGroup' => '',
            'promo' => '',
            'products' => [],
        ];

        /** @var Item[] $items */
        $items = $this->getAllItems($request);

        $quote = current($items)->getQuote();
        $customer = $quote->getCustomer();
        $apiRequestBody = array_merge($apiRequestBody, $this->getCustomerData($quote));

        if ($customer->getId()) {
            $apiRequestBody['customerGroup'] = $customer->getGroupId();
        }

        foreach ($items as $item) {
            $apiRequestBody['products'][] = [
                'priceWithTax' => round($item->getBasePriceInclTax(), 2),
                'priceWithoutTax' => round($item->getBasePrice(), 2),
                'discountAmount' => round($item->getBaseDiscountAmount(), 2),
                'quantity' => $item->getQty(),
                'weight' => $item->getWeight(),
                'origin' => '', // @todo in the next iterations
                'sku' => $item->getSku(),
                'categories' => $item->getProduct()->getCategoryIds(),
                'dimensions' => $this->getDimensionsData($item),
                'customAttributes' => $this->getCustomAttributesData($item)
            ];
        }

        $debugData['request'] = $apiRequestBody;

        try {
            $response = $this->calcuratesClient->getRates($apiRequestBody, $this->getStore());
            $debugData['result'] = $response;
        } catch (LocalizedException $e) {
            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            $response = [];
        }
        $this->_debug($debugData);

        return $this->parseResponse($response, $quote);
    }

    /**
     * @param string $regionId
     *
     * @return string|null
     */
    protected function getRegionCodeById($regionId)
    {
        if (!$regionId) {
            return null;
        }

        if (!empty($this->regionNamesCache[$regionId])) {
            return $this->regionNamesCache[$regionId];
        }

        $regionInstance = $this->_regionFactory->create();
        $this->regionResource->load($regionInstance, $regionId);

        return $this->regionNamesCache[$regionId] = $regionInstance->getName();
    }

    /**
     * @param string $methodId
     * @param array $responseRate
     * @param Result $result
     * @param string $carrierTitle
     */
    protected function processRate($methodId, array $responseRate, Result $result, $carrierTitle = '')
    {
        $rate = $this->_rateMethodFactory->create();
        $baseAmount = $this->priceCurrency->convert(
            $responseRate['rate']['cost'],
            null,
            $responseRate['rate']['currency']
        );
        $rate->setCarrier(self::CODE);
        $rate->setMethod($methodId);
        $rate->setMethodTitle($responseRate['name']);
        $rate->setCarrierTitle($carrierTitle);
        $rate->setInfoMessageEnabled((bool)$responseRate['message']);
        $rate->setInfoMessage($responseRate['message']);
        $rate->setCost($baseAmount);
        $rate->setPrice($baseAmount);
        $result->append($rate);
    }

    /**
     * Prepare shipping rate result based on response
     *
     * @param array $response
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return Result
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function parseResponse($response, $quote)
    {
        $result = $this->_rateFactory->create();

        try {
            if (!$response) {
                throw new \LogicException('Unable to get response');
            }

            foreach ($response as $origin) {
                $this->processFreeShipping($origin['freeShipping'], $result);
                $this->processFlatRates($origin['flatRates'], $result);
                $this->processTableRates($origin['tableRates'], $result);
                $this->processCarriers($origin['carriers'], $result);
                $this->processOrigin($origin['origin'], $quote);
            }
        } catch (\LogicException $exception) { //phpcs:ignore
            $this->processFailedRate($this->getConfigData('title'), $result);
        }

        return $result;
    }

    /**
     * @param array $origin
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return void
     */
    private function processOrigin($origin, $quote)
    {
        $quote->setData('calcurates_origin_data', json_encode($origin));
    }

    /**
     * @param array $flatRates
     * @param Result $result
     */
    private function processFlatRates(array $flatRates, Result $result)
    {
        foreach ($flatRates as $responseRate) {
            if (!$responseRate['success']) {
                if ($responseRate['message']) {
                    $this->processFailedRate($responseRate['name'], $result, $responseRate['message']);
                }
                continue;
            }

            $this->processRate(
                'flatRates_' . $responseRate['id'],
                $responseRate,
                $result
            );
        }
    }

    /**
     * @param array $freeShipping
     * @param Result $result
     */
    private function processFreeShipping(array $freeShipping, Result $result)
    {
        foreach ($freeShipping as $responseRate) {
            if (!$responseRate['success']) {
                if ($responseRate['message']) {
                    $this->processFailedRate($responseRate['name'], $result, $responseRate['message']);
                }
                continue;
            }

            $responseRate['rate'] = [
                'cost' => 0,
                'currency' => null,
            ];

            $this->processRate(
                'freeShipping' . $responseRate['id'],
                $responseRate,
                $result
            );
        }
    }

    /**
     * @param array $tableRates
     * @param Result $result
     */
    private function processTableRates(array $tableRates, Result $result)
    {
        foreach ($tableRates as $tableRate) {
            if (!$tableRate['success']) {
                if ($tableRate['message']) {
                    $this->processFailedRate($tableRate['name'], $result, $tableRate['message']);
                }

                continue;
            }

            foreach ($tableRate['methods'] as $responseRate) {
                if (!$responseRate['success']) {
                    if ($responseRate['message']) {
                        $this->processFailedRate($responseRate['name'], $result, $responseRate['message']);
                    }

                    continue;
                }

                $this->processRate(
                    'tableRate_' . $tableRate['id'] . '_' . $responseRate['id'],
                    $responseRate,
                    $result
                );
            }
        }
    }

    /**
     * @param array $carriers
     * @param Result $result
     */
    protected function processCarriers(array $carriers, Result $result)
    {
        foreach ($carriers as $carrier) {
            if (!$carrier['success']) {
                if ($carrier['message']) {
                    $this->processFailedRate($carrier['name'], $result, $carrier['message']);
                }

                continue;
            }

            foreach ($carrier['services'] as $responseRate) {
                if (!$responseRate['success']) {
                    if ($responseRate['message']) {
                        $this->processFailedRate($responseRate['name'], $result, $responseRate['message']);
                    }

                    continue;
                }

                $this->processRate(
                    'carrier_' . $carrier['id'] . '_' . $responseRate['id'],
                    $responseRate,
                    $result,
                    $carrier['name']
                );
            }
        }
    }

    /**
     * @param string $rateName
     * @param Result $result
     * @param string $message
     */
    private function processFailedRate(string $rateName, Result $result, string $message = '')
    {
        $error = $this->_rateErrorFactory->create();
        $error->setCarrier(self::CODE);
        $error->setCarrierTitle($rateName);

        if ($message) {
            $error->setErrorMessage($message);
        } else {
            $error->setErrorMessage($this->getConfigData(CalcuratesConfig::CONFIG_ERROR_MESSAGE));
        }

        $result->append($error);
    }

    /**
     * @param Item $item
     *
     * @return array
     */
    private function getDimensionsData(Item $item)
    {
        $data = $this->calcuratesConfig->getLinkedDimensionsAttributes();
        $this->processAttributes($data, $item);

        return $data;
    }

    /**
     * @param Item $item
     * @return array
     */
    private function getCustomAttributesData(Item $item)
    {
        $customAttributes = $this->calcuratesConfig->getCustomAttributes();
        $data = [];
        foreach ($customAttributes as $customAttribute) {
            $data[$customAttribute] = $item->getProduct()->getData($customAttribute);
        }

        return $data;
    }

    /**
     * @param array $data
     * @param Item $item
     */
    private function processAttributes(&$data, Item $item)
    {
        foreach ($data as $key => &$value) {
            if (\is_array($value)) {
                $this->processAttributes($value, $item);

                continue;
            }

            $value = $item->getProduct()->getData($value);
        }
    }

    /**
     * Collect customer information from shipping address
     *
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return array
     */
    private function getCustomerData(\Magento\Quote\Model\Quote $quote)
    {
        $customerData = [
            'contactName' => '',
            'companyName' => '',
            'contactPhone' => '',
        ];
        $shipAddress = $quote->getShippingAddress();

        $customerData['contactName'] = $shipAddress->getPrefix() . ' ';
        $customerData['contactName'] .= $shipAddress->getFirstname() ? $shipAddress->getFirstname() . ' ' : '';
        $customerData['contactName'] .= $shipAddress->getMiddlename() ? $shipAddress->getMiddlename() . ' ' : '';
        $customerData['contactName'] = trim($customerData['contactName'] . $shipAddress->getLastname());

        $customerData['companyName'] = $shipAddress->getCompany();
        $customerData['contactPhone'] = $shipAddress->getTelephone();

        return $customerData;
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
}
