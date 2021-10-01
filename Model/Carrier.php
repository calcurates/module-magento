<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model;

use Calcurates\ModuleMagento\Api\Client\CalcuratesClientInterface;
use Calcurates\ModuleMagento\Client\Command\CreateShippingLabelCommand;
use Calcurates\ModuleMagento\Client\Command\GetAllShippingOptionsCommand;
use Calcurates\ModuleMagento\Client\Http\ApiException;
use Calcurates\ModuleMagento\Client\Request\RateRequestBuilder;
use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Calcurates\ModuleMagento\Model\Carrier\Validator\RateRequestValidator;
use Calcurates\ModuleMagento\Model\Shipment\CustomPackagesProvider;
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
    public const CODE = 'calcurates';

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
     * @var CustomPackagesProvider
     */
    private $customPackagesProvider;

    /**
     * @var CreateShippingLabelCommand
     */
    private $createShippingLabelCommand;

    /**
     * @var GetAllShippingOptionsCommand
     */
    private $getAllShippingOptionsCommand;

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
     * @param RateRequestValidator $rateRequestValidator
     * @param CustomPackagesProvider $customPackagesProvider
     * @param CreateShippingLabelCommand $createShippingLabelCommand
     * @param GetAllShippingOptionsCommand $getAllShippingOptionsCommand
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
        CustomPackagesProvider $customPackagesProvider,
        CreateShippingLabelCommand $createShippingLabelCommand,
        GetAllShippingOptionsCommand $getAllShippingOptionsCommand,
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
        $this->customPackagesProvider = $customPackagesProvider;
        $this->createShippingLabelCommand = $createShippingLabelCommand;
        $this->getAllShippingOptionsCommand = $getAllShippingOptionsCommand;
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
        $ignoreVirtual = $this->getConfigFlag('ignore_virtual');
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                /* @var $item Item */
                if ($item->getParentItem() || ($ignoreVirtual && $item->getProduct()->isVirtual())) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() || ($ignoreVirtual && $item->getProduct()->isVirtual())) {
                            continue;
                        }
                        $items[] = $child;
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
        } catch (ApiException $e) {
            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            throw $e; // throws for fallback rate
        } catch (\Exception $e) {
            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            $response = [];
        } finally {
            $this->_debug($debugData);
        }

        return $this->ratesResponseProcessor->processResponse($response, $quote);
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return $this->getAllShippingOptionsCommand->getShippingOptions($this->getStore());
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
        return true;
    }

    /**
     * Get tracking
     *
     * @param @param string|string[] $trackings
     * @return \Magento\Shipping\Model\Tracking\Result|null
     */
    public function getTracking($trackings)
    {
        return null;
    }

    /**
     * @param DataObject|null $params
     * @return array
     */
    public function getContainerTypes(\Magento\Framework\DataObject $params = null)
    {
        $shipment = $this->registry->registry('current_shipment');
        $customPackages = $this->customPackagesProvider->getCustomPackages($shipment);
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
