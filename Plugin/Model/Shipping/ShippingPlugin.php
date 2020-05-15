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
use Calcurates\ModuleMagento\Model\Config\Source\OtherShippingMethodsActionSource;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Shipping;

class ShippingPlugin
{
    /**
     * @var Config
     */
    private $config;

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
    public function aroundCollectRates(
        Shipping $subject,
        \Closure $proceed,
        RateRequest $request
    ) {
        $action = $this->config->getOtherShippingMethodsAction($request->getStoreId());

        if ($action == OtherShippingMethodsActionSource::ALWAYS_SHOW) {
            return $proceed($request);
        }

        if ($action == OtherShippingMethodsActionSource::ALWAYS_HIDE
            || $action == OtherShippingMethodsActionSource::SHOW_IF_ERROR_OR_EXCEEDS_TIMEOUT
        ) {
            $request->setLimitCarrier(Carrier::CODE);
        }

        if ($action == OtherShippingMethodsActionSource::SHOW_IF_ERROR_OR_EXCEEDS_TIMEOUT) {
            try {
                return $proceed($request);
            } catch (ApiException $e) {
                $request->setLimitCarrier(null);
                $request->setSkipCalcurates(true);
            }
        }

        return $proceed($request);
    }
}
