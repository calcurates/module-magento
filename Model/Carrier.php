<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model;

use Calcurates\ModuleMagento\Model\Config as CalcuratesConfig;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region as RegionResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;
use Zend\Http\Exception\RuntimeException as HttpRuntimeException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Carrier extends AbstractCarrier implements CarrierInterface
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
     * @var ClientFactory
     */
    private $httpClientFactory;

    /**
     * @var \Calcurates\ModuleMagento\Model\Config
     */
    private $calcuratesConfig;

    /**
     * @var RegionResource
     */
    private $regionResource;

    /**
     * @var ResultFactory
     */
    private $rateFactory;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var MethodFactory
     */
    private $rateMethodFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ClientFactory $httpClientFactory,
        CalcuratesConfig $calcuratesConfig,
        RegionResource $regionResource,
        ResultFactory $rateFactory,
        RegionFactory $regionFactory,
        MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);

        $this->httpClientFactory = $httpClientFactory;
        $this->calcuratesConfig = $calcuratesConfig;
        $this->regionResource = $regionResource;
        $this->rateFactory = $rateFactory;
        $this->regionFactory = $regionFactory;
        $this->rateMethodFactory = $rateMethodFactory;
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
            'address' => $streetArray[0],
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
                'feature' => $this->getAttributesData($item),
            ];
        }

        $debugData['request'] = $apiRequestBody;

        try {
            $client = $this->httpClientFactory->create();
            $composerPackage = $this->calcuratesConfig->getComposerPackage();

            $client->addHeader('User-Agent', $composerPackage->getName().'/'.$composerPackage->getVersion());
            $client->addHeader('X-API-Key', $this->calcuratesConfig->getCalcuratesToken());
            $client->addHeader('Content-Type', 'application/json');
            $client->post($this->getAPIUrl().'/rates', \Zend_Json::encode($apiRequestBody));

            if ($client->getStatus() >= 400) {
                throw new HttpRuntimeException($client->getBody(), $client->getStatus());
            }
            $debugData['result'] = $client->getBody();
            $response = \Zend_Json::decode($client->getBody());
        } catch (\Throwable $e) {
            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            $response = [];
        }
        $this->_debug($debugData);

        return $this->parseResponse($response);
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

        $regionInstance = $this->regionFactory->create();
        $this->regionResource->load($regionInstance, $regionId);

        return $this->regionNamesCache[$regionId] = $regionInstance->getName();
    }

    /**
     * @param string $methodId
     * @param array $responseRate
     * @param Result $result
     */
    protected function processRate($methodId, array $responseRate, Result $result)
    {
        if ($responseRate['cost'] !== null) {
            $rate = $this->rateMethodFactory->create();
            $rate->setCarrier(self::CODE);
            $rate->setMethod($methodId);
            $rate->setMethodTitle($responseRate['name']);
            $rate->setCarrierTitle('');
            $rate->setInfoMessageEnabled((bool)$responseRate['message']);
            $rate->setInfoMessage($responseRate['message']);
            $rate->setCost($responseRate['cost']);
            $rate->setPrice($responseRate['cost']);
            $result->append($rate);
        } elseif (!$responseRate['success']) {
            $this->processFailedRate($responseRate['name'], $result, $responseRate['message']);
        }
    }

    /**
     * @return string
     */
    protected function getAPIUrl()
    {
        return rtrim($this->getConfigData(CalcuratesConfig::CONFIG_API_URL), '/').'/api/v1';
    }

    /**
     * Prepare shipping rate result based on response
     *
     * @param array $response
     *
     * @return Result
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function parseResponse($response)
    {
        $result = $this->rateFactory->create();
        try {
            if (!$response) {
                throw new \LogicException();
            }

            foreach ($response as $origin) {
                $this->processSingleRates($origin, $result);
                $this->processTableRates($origin['tableRates'], $result);
                $this->processCarriers($origin['carriers'], $result);
            }
        } catch (\LogicException $exception) { //phpcs:ignore
            $this->processFailedRate($this->getConfigData('title'), $result);
        }

        return $result;
    }

    /**
     * @param array $origin
     * @param Result $result
     */
    private function processSingleRates(array $origin, Result $result)
    {
        foreach (['flatRates', 'freeShipping'] as $shippingType) {
            foreach ($origin[$shippingType] as $responseRate) {
                if (isset($responseRate['rate'])) {
                    $rateData = array_merge($responseRate['rate'], $responseRate);
                    unset($rateData['rate']);
                } else {
                    $rateData = $responseRate;
                    $rateData['cost'] = 0;
                }

                $this->processRate(
                    $shippingType.'_'.$rateData['id'],
                    $rateData,
                    $result
                );
            }
        }
    }

    /**
     * @param array $tableRates
     * @param Result $result
     */
    private function processTableRates(array $tableRates, Result $result)
    {
        foreach ($tableRates as $tableRate) {
            if (!isset($tableRate['success'])) {
                return;
            }

            if (!$tableRate['success']) {
                $this->processFailedRate($tableRate['name'], $result, $tableRate['message']);

                return;
            }

            $tableRatesMeta = $tableRate;
            unset($tableRatesMeta['methods']);
            $baseMethodId = 'tableRate_'.$tableRate['id'].'_';

            foreach ($tableRate['methods'] as $responseRate) {
                $rateData = array_merge($tableRatesMeta, $responseRate['rate'], $responseRate);
                $rateData['message'] = $tableRate['message'];
                unset($rateData['rate']);

                $this->processRate(
                    $baseMethodId.$rateData['id'],
                    $rateData,
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
            if (!isset($carrier['success'])) {
                return;
            }

            if (!$carrier['success']) {
                $this->processFailedRate($carrier['name'], $result, $carrier['message']);

                return;
            }

            $carrierMeta = $carrier;
            unset($carrierMeta['services']);
            $baseMethodId = 'carrier_'.$carrier['id'].'_';

            foreach ($carrier['services'] as $service) {
                $rateData = array_merge($carrierMeta, $service, $service['rate']);
                $rateData['message'] = $carrier['message'];
                unset($rateData['rate']);

                $this->processRate(
                    $baseMethodId.$rateData['id'],
                    $rateData,
                    $result
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
    private function getAttributesData(Item $item)
    {
        $data = $this->calcuratesConfig->getLinkedVolumetricWeightAttributes();
        $this->processAttributes($data, $item);

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

        $customerData['contactName'] = $shipAddress->getPrefix().' ';
        $customerData['contactName'] .= $shipAddress->getFirstname() ? $shipAddress->getFirstname().' ' : '';
        $customerData['contactName'] .= $shipAddress->getMiddlename() ? $shipAddress->getMiddlename().' ' : '';
        $customerData['contactName'] = trim($customerData['contactName'].$shipAddress->getLastname());

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
}
