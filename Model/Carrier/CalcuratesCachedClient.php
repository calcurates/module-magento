<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Carrier;

use Calcurates\ModuleMagento\Api\Client\CalcuratesClientInterface;
use Calcurates\ModuleMagento\Client\Http\ApiException;
use Magento\Framework\Exception\LocalizedException;

class CalcuratesCachedClient implements CalcuratesClientInterface
{
    /**
     * @var CalcuratesClientInterface
     */
    private $calcuratesClient;

    /**
     * @var RatesRequestCache
     */
    private $cache;

    /**
     * CalcuratesCachedClient constructor.
     * @param CalcuratesClientInterface $calcuratesClient
     * @param RatesRequestCache $cache
     */
    public function __construct(CalcuratesClientInterface $calcuratesClient, RatesRequestCache $cache)
    {
        $this->calcuratesClient = $calcuratesClient;
        $this->cache = $cache;
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     */
    public function getShippingCarriersWithServices($storeId)
    {
        return $this->calcuratesClient->getShippingCarriersWithServices($storeId);
    }

    /**
     * @param string $url
     * @param int $storeId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLabelContent(string $url, int $storeId): string
    {
        return $this->calcuratesClient->getLabelContent($url, $storeId);
    }

    /**
     * @param string $serviceId
     * @param string $tracking
     * @param int|\Magento\Framework\App\ScopeInterface|string $storeId
     * @return array
     */
    public function getTrackingInfo(string $serviceId, string $tracking, $storeId): array
    {
        return $this->calcuratesClient->getTrackingInfo($serviceId, $tracking, $storeId);
    }

    /**
     * @param array $request
     * @param int|\Magento\Framework\App\ScopeInterface|string $storeId
     * @return array
     */
    public function createShippingLabel(array $request, $storeId): array
    {
        return $this->calcuratesClient->createShippingLabel($request, $storeId);
    }

    /**
     * @param array $request
     * @param int|\Magento\Framework\App\ScopeInterface|string $storeId
     * @return array
     */
    public function getRates(array $request, $storeId): array
    {
        $rates = $this->cache->getCachedData($request, $storeId);
        if (null === $rates) {
            try {
                $rates = $this->calcuratesClient->getRates($request, $storeId);
            } catch (LocalizedException|ApiException $e) {
                $rates = [];
            }
            $this->cache->saveCachedData($request, $storeId, $rates);
        }

        return $rates;
    }

    /**
     * @param array $request
     * @param int|\Magento\Framework\App\ScopeInterface|string $storeId
     * @return array
     */
    public function getRatesSimple(array $request, $storeId): array
    {
        return $this->calcuratesClient->getRatesSimple($request, $storeId);
    }

    /**
     * @param array $request
     * @param int|\Magento\Framework\App\ScopeInterface|string $storeId
     * @return array
     */
    public function getRatesSplitCheckout(array $request, $storeId): array
    {
        $rates = $this->cache->getCachedData($request, $storeId);
        if (null === $rates) {
            try {
                $rates = $this->calcuratesClient->getRatesSplitCheckout($request, $storeId);
            } catch (LocalizedException|ApiException $e) {
                $rates = [];
            }
            $this->cache->saveCachedData($request, $storeId, $rates);
        }

        return $rates;
    }

    /**
     * @param int|\Magento\Framework\App\ScopeInterface|string $storeId
     * @return array
     */
    public function getCustomPackages($storeId): array
    {
        return $this->calcuratesClient->getCustomPackages($storeId);
    }

    /**
     *
     * @param string $type
     * @param int|\Magento\Framework\App\ScopeInterface|string $storeId
     * @return array
     */
    public function getShippingOptions(string $type, $storeId): array
    {
        return $this->calcuratesClient->getShippingOptions($type, $storeId);
    }

    /**
     * @param array $request
     * @param int $storeId
     * @return array
     */
    public function populateOrderInfo(array $request, $storeId): array
    {
        return $this->calcuratesClient->populateOrderInfo($request, $storeId);
    }
}
