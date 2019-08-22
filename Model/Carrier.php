<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_Integration
 */

namespace Calcurates\Integration\Model;

use Calcurates\Integration\Model\Config as CalcuratesConfig;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region as RegionResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

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

    const CALCURATES_API_PATH = '/calculator/calculate-magento';

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
     * @var \Calcurates\Integration\Model\Config
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
                /* @var $item \Magento\Quote\Model\Quote\Item */
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

        /** @var \Magento\Quote\Model\Quote\Item[] $items */
        $items = $this->getAllItems($request);

        $quote = current($items)->getQuote();
        $customer = $quote->getCustomer();

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
            ];
        }

        $debugData['request'] = $apiRequestBody;

        try {
            $client = $this->httpClientFactory->create();
            $composerPackage = $this->calcuratesConfig->getComposerPackage();

            $client->addHeader('User-Agent', $composerPackage->getName() . '/' . $composerPackage->getVersion());
            $client->addHeader('X-API-Key', $this->calcuratesConfig->getCalcuratesToken());
            $client->addHeader('Content-Type', 'application/json');
            $client->post($this->getAPIUrl() . self::CALCURATES_API_PATH, \Zend_Json::encode($apiRequestBody));

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
     * @return string
     */
    protected function getAPIUrl()
    {
        return rtrim($this->getConfigData(CalcuratesConfig::CONFIG_API_URL), '/');
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
    protected function parseResponse($response = [])
    {
        $result = $this->rateFactory->create();
        try {
            if (!isset($response['origins'])) {
                throw new \LogicException();
            }

            foreach ($response['origins'] as $origin) {
                $this->processFlatAndFreeRates($origin, $result);
                $this->processTableRates($origin, $result);
            }
        } catch (\LogicException $exception) { //phpcs:ignore
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier(self::CODE);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData(CalcuratesConfig::CONFIG_ERROR_MESSAGE));
            $result->append($error);
        }

        return $result;
    }

    /**
     * @param array $origin
     * @param Result $result
     */
    protected function processFlatAndFreeRates(array $origin, Result $result)
    {
        foreach (['flatRate', 'freeShipping'] as $shippingType) {
            foreach ($origin[$shippingType . 's'] as $responseRate) {
                $shippingOption = $responseRate[$shippingType]['shippingOption'];
                $methodId = self::CODE . '_' . $shippingType . '_' . $shippingOption['id'];

                $this->processRate($shippingOption, $responseRate, $methodId, $result);
            }
        }
    }

    /**
     * @param array $origin
     * @param Result $result
     */
    protected function processTableRates(array $origin, Result $result)
    {
        foreach ($origin['tableRates'] as $tableRate) {
            $shippingOption = $tableRate['shippingOption'];

            foreach ($tableRate['tableRateMethods'] as $responseRate) {
                $tableRateMethod = $responseRate['tableRateMethod'];
                $methodId = self::CODE . '_tableRate_' . $shippingOption['id'] . '_' . $tableRateMethod['id'];

                $this->processRate($shippingOption, $responseRate, $methodId, $result);
            }
        }
    }

    /**
     * @param array $shippingOption
     * @param array $responseRate
     * @param string $methodId
     * @param Result $result
     */
    protected function processRate(array $shippingOption, array $responseRate, $methodId, Result $result)
    {
        if ($responseRate['cost'] !== null) {
            $rate = $this->rateMethodFactory->create();
            $rate->setCarrier(self::CODE);
            $rate->setMethod($methodId);
            $rate->setMethodTitle($shippingOption['name']);
            $rate->setCarrierTitle('');
            $rate->setInfoMessageEnabled($shippingOption['infoMessageEnabled']);
            $rate->setInfoMessage($shippingOption['infoMessage']);
            $rate->setCost($responseRate['cost']);
            $rate->setPrice($responseRate['cost']);
            $result->append($rate);
        } elseif ($shippingOption['errorMessageEnabled']) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier(self::CODE);
            $error->setCarrierTitle($shippingOption['name']);

            if ($shippingOption['errorMessage']) {
                $error->setErrorMessage($shippingOption['errorMessage']);
            } else {
                $error->setErrorMessage($this->getConfigData(CalcuratesConfig::CONFIG_ERROR_MESSAGE));
            }

            $result->append($error);
        }
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
