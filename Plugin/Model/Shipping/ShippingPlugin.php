<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Model\Shipping;

use Calcurates\ModuleMagento\Client\Http\ApiException;
use Calcurates\ModuleMagento\Model\Carrier;
use Calcurates\ModuleMagento\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Shipping;
use Magento\Framework\App\Request\DataPersistorInterface;

class ShippingPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $shippingMethodsForFallback = [];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var bool
     */
    private $showFallBackMethod = false;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * ShippingPlugin constructor.
     * @param Config $config
     * @param ScopeConfigInterface $scopeConfig
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Config $config,
        ScopeConfigInterface $scopeConfig,
        DataPersistorInterface $dataPersistor
    ) {
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @param Shipping $subject
     * @param \Closure $proceed
     * @param RateRequest $request
     * @return Shipping
     */
    public function aroundCollectRates(Shipping $subject, \Closure $proceed, RateRequest $request)
    {
        try {
            // make Calcurates as first carrier for process
            $allCarriers = $this->getAllCarrierCodesCalcuratesFirst($request->getStoreId());
            $request->setIsAllCarriersCalcuratesFirst(false);
            if (!$request->getLimitCarrier()) {
                $request->setLimitCarrier($allCarriers);
                $request->setIsAllCarriersCalcuratesFirst(true);
            }

            return $proceed($request);
        } catch (ApiException $e) {
            $request->setIsAllCarriersCalcuratesFirst(false);
            $shippingMethodsForFallback = $this->getShippingMethodsForFallback($request->getStoreId());
            if ($shippingMethodsForFallback) {
                $request->setLimitCarrier($shippingMethodsForFallback);
            }
            $request->setSkipCalcurates(true);
        }

        return $proceed($request);
    }

    /**
     * @param Shipping $subject
     * @param \Closure $proceed
     * @param string $carrierCode
     * @param RateRequest $request
     * @return Shipping
     */
    public function aroundCollectCarrierRates(Shipping $subject, \Closure $proceed, $carrierCode, $request)
    {
        $shippingMethodsForFallback = $this->getShippingMethodsForFallback($request->getStoreId());
        if ($this->config->isActive($request->getStoreId())
            && in_array($carrierCode, $shippingMethodsForFallback)
            && $request->getIsAllCarriersCalcuratesFirst()
            && !$this->showFallBackMethod
        ) {
            return $subject;
        }
        if ($this->dataPersistor->get('vat_id')) {
            $request->setVatId($this->dataPersistor->get('vat_id'));
        }
        $result = $proceed($carrierCode, $request);
        if ($carrierCode == Carrier::CODE) {
            $calcuratesRates = $result->getResult()->getRatesByCarrier(Carrier::CODE);
            if (count($calcuratesRates) === 1) {
                $rate = reset($calcuratesRates);
                if (!$rate->getMethodTitle()
                    && $rate->getErrorMessage()
                    == (string) $this->config->getMissingAddressMessage($request->getStoreId())
                ) {
                    $this->showFallBackMethod = false;
                } elseif (!$rate->getMethodTitle()) {
                    $this->showFallBackMethod = true;
                }
            }
        }
        return $result;
    }

    /**
     * @param int $storeId
     * @return array
     */
    private function getShippingMethodsForFallback($storeId)
    {
        if (!array_key_exists($storeId, $this->shippingMethodsForFallback)) {
            $this->shippingMethodsForFallback[$storeId] = $this->config->getShippingMethodsForFallback($storeId);
        }

        return $this->shippingMethodsForFallback[$storeId];
    }

    /**
     * @param int $storeId
     * @return array
     */
    private function getAllCarrierCodesCalcuratesFirst($storeId)
    {
        $carriers = $this->scopeConfig->getValue(
            'carriers',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        unset($carriers[Carrier::CODE]);
        $carriers = array_keys($carriers);
        array_unshift($carriers, Carrier::CODE);

        return $carriers;
    }
}
