<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\Model\Shipping;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Shipping;

class ShippingAddEstimateFlagToRequestPlugin
{
    const IS_ESTIMATE_ONLY_FLAG = 'is_estimate_only_flag';

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
     * @return array
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
        $referer = $this->request->getHeader('referer');

        return $this->request->isXmlHttpRequest() && strpos($referer, 'checkout/cart') !== false;
    }
}
