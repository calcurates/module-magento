<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Model\Shipping;

use Calcurates\ModuleMagento\Client\Http\ApiException;
use Calcurates\ModuleMagento\Model\Config;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Shipping;

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
     * ShippingPlugin constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
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
            /**
             * @TODO: Need optimization: Calcurates API runs not always first, and when it answers with error,
             * before it, some rates could be already counted. Need collect Calcurates first.
             */
            return $proceed($request);
        } catch (ApiException $e) {
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
            && !$request->getSkipCalcurates()
            && in_array($carrierCode, $shippingMethodsForFallback)
        ) {
            return $subject;
        }

        return $proceed($carrierCode, $request);
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
}
