<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Carrier\Validator;

use Magento\Quote\Model\Quote\Address\RateRequest;

class RateRequestValidator
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * RequestRatesValidator constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(\Magento\Framework\App\RequestInterface $request)
    {
        $this->request = $request;
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
        return $this->request->getModuleName() === 'checkout'
            && $controller === 'cart'
            && $this->request->getActionName() === 'add';
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
