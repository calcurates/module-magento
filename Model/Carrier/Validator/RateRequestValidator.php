<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Carrier\Validator;

use Magento\Directory\Helper\Data;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Calcurates\ModuleMagento\Plugin\Model\Shipping\ShippingAddEstimateFlagToRequestPlugin;

class RateRequestValidator
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var Data
     */
    private $helper;

    /**
     * RequestRatesValidator constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Data $helper
     */
    public function __construct(\Magento\Framework\App\RequestInterface $request, Data $helper)
    {
        $this->request = $request;
        $this->helper = $helper;
    }

    /**
     * @param RateRequest $request
     * @return bool
     */
    public function validate(RateRequest $request)
    {
        return !$this->isAddToCartRequest() && $this->validateRequest($request);
    }

    /**
     * Workaround: disabling request rates for each add-to-cart from customer, who have valid shipping address
     * attached to cart
     *
     * @return bool
     */
    private function isAddToCartRequest()
    {
        $controller = method_exists($this->request, 'getControllerName') ?
            $this->request->getControllerName() :
            '';
        $isAddToCart = $this->request->getModuleName() === 'checkout'
            && $controller === 'cart'
            && $this->request->getActionName() === 'add';

        $isRemoveFromCart = $this->request->getModuleName() === 'checkout'
            && $controller === 'sidebar'
            && $this->request->getActionName() === 'removeItem';

        return $isAddToCart || $isRemoveFromCart;
    }

    /**
     * @param RateRequest $request
     * @return bool
     */
    private function validateRequest(RateRequest $request)
    {
        $isRegionRequired = $this->isRegionRequired($request->getDestCountryId());
        $valid = (!$isRegionRequired || !empty($request->getDestRegionCode()))
            && (
                $this->helper->isZipCodeOptional($request->getDestCountryId())
                || !empty($request->getDestPostcode())
            )
            && !empty($request->getDestCountryId());
        if ($valid && !$request->getData(ShippingAddEstimateFlagToRequestPlugin::IS_ESTIMATE_ONLY_FLAG)) {
            return $valid && $request->getDestStreet() && $request->getDestCity();
        }
        return $valid;
    }

    /**
     * @param string|null $country
     * @return bool
     */
    private function isRegionRequired($country)
    {
        if (!$country) {
            return false;
        }

        $countriesWithStatesRequired = $this->helper->getCountriesWithStatesRequired();

        return in_array($country, $countriesWithStatesRequired);
    }
}
