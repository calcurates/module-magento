<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\Model\Shipping;

use Magento\Framework\App\Area;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Shipping;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;

class ShippingAddEstimateFlagToRequestPlugin
{
    public const IS_ESTIMATE_ONLY_FLAG = 'is_estimate_only_flag';

    public const IS_GRAPHQL_ESTIMATE_FLAG = 'is_graphql_estimate_flag';

    private const ESTIMATE_MUTATIONS = [
        'estimateShippingMethods',
        'estimateTotals',
    ];

    /**
     * @var RequestInterface|Http
     */
    private $request;

    /**
     * @var State
     */
    private $appState;

    /**
     * @param RequestInterface $request
     * @param State $appState
     */
    public function __construct(RequestInterface $request, State $appState)
    {
        $this->request = $request;
        $this->appState = $appState;
    }

    /**
     * @param Shipping $subject
     * @param RateRequest $request
     * @return RateRequest[]
     */
    public function beforeCollectRates(Shipping $subject, RateRequest $request): array
    {
        $isGraphQlEstimate = $this->isGraphQlEstimate();
        $request->setData(self::IS_GRAPHQL_ESTIMATE_FLAG, $isGraphQlEstimate);
        $request->setData(self::IS_ESTIMATE_ONLY_FLAG, $isGraphQlEstimate || $this->isAjaxFromCartPage());
        return [$request];
    }

    /**
     * @return bool
     */
    public function isAjaxFromCartPage(): bool
    {
        $pathInfo = $this->request->getPathInfo();
        if ($pathInfo && strpos($pathInfo, 'paymentservicespaypal/smartbuttons/shippingcallback') !== false) {
            return true;
        }

        if (!$this->request->isXmlHttpRequest()) {
            return false;
        }

        $referer = $this->request->getHeader('referer');
        if (!$referer) {
            return false;
        }

        return strpos($referer, 'checkout/cart') !== false;
    }

    /**
     * @return bool
     */
    private function isGraphQlEstimate(): bool
    {
        try {
            if ($this->appState->getAreaCode() !== Area::AREA_GRAPHQL) {
                return false;
            }
        } catch (LocalizedException $e) {
            return false;
        }

        if (!method_exists($this->request, 'getContent')) {
            return false;
        }

        try {
            $content = (string)$this->request->getContent();
        } catch (\Throwable $e) {
            return false;
        }

        return $content !== '' && $this->containsEstimateMutation($content);
    }

    /**
     * @param string $content
     * @return bool
     */
    private function containsEstimateMutation(string $content): bool
    {
        $payload = json_decode($content, true);

        if (!is_array($payload)) {
            return false;
        }

        $operations = isset($payload['query']) ? [$payload] : $payload;

        foreach ($operations as $operation) {
            $query = is_array($operation) ? ($operation['query'] ?? null) : null;
            if (!is_string($query)) {
                continue;
            }

            foreach (self::ESTIMATE_MUTATIONS as $mutation) {
                if (str_contains($query, $mutation)) {
                    return true;
                }
            }
        }
        return false;
    }
}
