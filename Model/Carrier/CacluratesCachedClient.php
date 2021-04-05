<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Carrier;

use Calcurates\ModuleMagento\Api\Client\CalcuratesClientInterface;

class CacluratesCachedClient implements CalcuratesClientInterface
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
     * CacluratesCachedClient constructor.
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
     * @return string
     */
    public function getLabelContent($url)
    {
        return $this->calcuratesClient->getLabelContent($url);
    }

    /**
     * @param string $serviceId
     * @param string $tracking
     * @param int|\Magento\Framework\App\ScopeInterface|string $storeId
     * @return array
     */
    public function getTrackingInfo($serviceId, $tracking, $storeId)
    {
        return $this->calcuratesClient->getTrackingInfo($serviceId, $tracking, $storeId);
    }

    /**
     * @param array $request
     * @param int|\Magento\Framework\App\ScopeInterface|string $storeId
     * @return array
     */
    public function createShippingLabel($request, $storeId)
    {
        return $this->calcuratesClient->createShippingLabel($request, $storeId);
    }

    /**
     * @param array $request
     * @param int|\Magento\Framework\App\ScopeInterface|string $storeId
     * @return array|bool
     */
    public function getRates($request, $storeId)
    {
        $rates = $this->cache->getCachedData($request, $storeId);
        if ($rates === false) {
            $rates = $this->calcuratesClient->getRates($request, $storeId);
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
     * @param int|\Magento\Framework\App\ScopeInterface|string $storeId
     * @return array
     */
    public function getCustomPackages($storeId)
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
}
