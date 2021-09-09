<?php

declare(strict_types=1);

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */


namespace Calcurates\ModuleMagento\Plugin\Model\Shipping;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Shipping;

class ShippingAddEstimateFlagToRequestPlugin
{
    public const IS_ESTIMATE_ONLY_FLAG = 'is_estimate_only_flag';

    /**
     * @var RequestInterface|Http
     */
    private $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @param Shipping $subject
     * @param RateRequest $request
     * @return RateRequest[]
     */
    public function beforeCollectRates(Shipping $subject, RateRequest $request): array
    {
        $request->setData(self::IS_ESTIMATE_ONLY_FLAG, $this->isAjaxFromCartPage());

        return [$request];
    }

    /**
     * @return bool
     */
    public function isAjaxFromCartPage(): bool
    {
        if (!$this->request->isXmlHttpRequest()) {
            return false;
        }

        $referer = $this->request->getHeader('referer');
        if (!$referer) {
            return false;
        }

        return strpos($referer, 'checkout/cart') !== false;
    }
}
