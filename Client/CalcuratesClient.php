<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client;

use Calcurates\ModuleMagento\Api\Client\CalcuratesClientInterface;
use Calcurates\ModuleMagento\Client\Http\ApiException;
use Calcurates\ModuleMagento\Client\Http\HttpClient;
use Calcurates\ModuleMagento\Model\Config;
use Magento\Framework\Exception\LocalizedException;

class CalcuratesClient implements CalcuratesClientInterface
{
    /**
     * @var Config
     */
    private $calcuratesConfig;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * CalcuratesClient constructor.
     * @param HttpClient $httpClient
     * @param Config $calcuratesConfig
     */
    public function __construct(
        HttpClient $httpClient,
        Config $calcuratesConfig
    ) {
        $this->calcuratesConfig = $calcuratesConfig;
        $this->httpClient = $httpClient;
        $this->init();
    }

    /**
     * initialize http client
     */
    protected function init(): void
    {
        $composerPackage = $this->calcuratesConfig->getComposerPackage();
        $this->httpClient
            ->addHeader('User-Agent', $composerPackage->getName() . '/' . $composerPackage->getVersion())
            ->addHeader('X-API-Key', $this->calcuratesConfig->getCalcuratesToken());
    }

    /**
     * @param string $shippingCarrierId
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     */
    public function getShippingServices($shippingCarrierId, $storeId)
    {
        try {
            $response = $this->httpClient->get(
                $this->getAPIUrl($storeId) . '/shipping-options/' . $shippingCarrierId . '/services'
            );
            $response = \Zend_Json::decode($response);
        } catch (\Throwable $e) {
            $response = [];
        }

        $shippingServices = [];
        foreach ($response as $item) {
            $shippingServices[] = [
                'value' => $item['id'],
                'label' => $item['name']
            ];
        }

        return $shippingServices;
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     */
    public function getShippingCarriersWithServices($storeId)
    {
        try {
            $response = $this->httpClient->get(
                $this->getAPIUrl($storeId) . '/shipping-options/carriers'
            );
            $response = \Zend_Json::decode($response);
        } catch (\Throwable $e) {
            $response = [];
        }

        $shippingCarriers = [];
        foreach ($response as $item) {
            $shippingCarrier = [
                'id' => $item['id'],
                'label' => $item['carrierName'],
                'services' => []
            ];

            foreach ($item['services'] as $service) {
                $shippingCarrier['services'][] = [
                    'value' => $service['id'],
                    'label' => $service['name']
                ];
            }
            if ($shippingCarrier['services']) {
                $shippingCarriers[] = $shippingCarrier;
            }
        }

        return $shippingCarriers;
    }

    /**
     * @param string $url
     * @return string
     * @throws LocalizedException
     */
    public function getLabelContent($url)
    {
        try {
            $response = $this->httpClient->get($url);
        } catch (\Throwable $e) {
            throw new LocalizedException(__('Cannot getting label from API Calcurates %1', $e->getMessage()));
        }

        return $response;
    }

    /**
     * @param string $serviceId
     * @param string $tracking
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     * @throws LocalizedException
     */
    public function getTrackingInfo($serviceId, $tracking, $storeId)
    {
        try {
            $query = http_build_query([
                'serviceId' => $serviceId,
                'trackingNumber' => $tracking
            ]);
            $response = $this->httpClient->get($this->getAPIUrl($storeId) . '/tracking?' . $query);
            $response = \Zend_Json::decode($response);
        } catch (\Throwable $e) {
            throw new LocalizedException(__('Cannot getting tracking from API Calcurates %1', $e->getMessage()));
        }

        return $response;
    }

    /**
     * @param array $request
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     * @throws LocalizedException
     */
    public function createShippingLabel($request, $storeId)
    {
        try {
            $response = $this->httpClient->post(
                $this->getAPIUrl($storeId) . '/labels',
                \Zend_Json::encode($request)
            );
            $response = \Zend_Json::decode($response);
        } catch (\Throwable $e) {
            throw new LocalizedException(__('Cannot create label with API Calcurates %1', $e->getMessage()));
        }

        return $response;
    }

    /**
     * @param $request
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     * @throws LocalizedException
     * @throws ApiException
     */
    public function getRates($request, $storeId)
    {
        $timeout = $this->calcuratesConfig->getApiGetRatesTimeout($storeId);
        try {
            $this->httpClient->setTimeout($timeout);
            $response = $this->httpClient->post(
                $this->getAPIUrl($storeId) . '/rates',
                \Zend_Json::encode($request)
            );
            $this->httpClient->setTimeout(null);
            $response = \Zend_Json::decode($response);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new LocalizedException(__('Cannot getting rates with API Calcurates %1', $e->getMessage()));
        }

        return $response;
    }

    /**
     * @param array $request
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     * @throws LocalizedException
     * @throws ApiException
     */
    public function getRatesSimple(array $request, $storeId): array
    {
        $timeout = $this->calcuratesConfig->getApiGetRatesTimeout($storeId);
        try {
            $this->httpClient->setTimeout($timeout);
            $response = $this->httpClient->post(
                $this->getAPIUrl($storeId) . '/rates-simple',
                \Zend_Json::encode($request)
            );
            $this->httpClient->setTimeout(null);
            $response = \Zend_Json::decode($response);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new LocalizedException(__('Cannot getting rates with API Calcurates %1', $e->getMessage()));
        }

        return $response;
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return string
     */
    protected function getApiUrl($storeId)
    {
        return rtrim($this->calcuratesConfig->getApiUrl($storeId), '/') . '/api/magento2';
    }

    /**
     * @param int|\Magento\Framework\App\ScopeInterface|string $storeId
     * @return array
     */
    public function getCustomPackages($storeId)
    {
        try {
            $response = $this->httpClient->get(
                $this->getAPIUrl($storeId) . '/custom-packages'
            );
            $response = \Zend_Json::decode($response);
        } catch (\Throwable $e) {
            $response = [];
        }

        return $response;
    }

    /**
     *
     * @param string $type
     * @param int|\Magento\Framework\App\ScopeInterface|string $storeId
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getShippingOptions(string $type, $storeId): array
    {
        $allowedTypes = [
            self::TYPE_CARRIERS,
            self::TYPE_TABLE_RATES,
            self::TYPE_FREE_SHIPPING,
            self::TYPE_FLAT_RATES,
            self::TYPE_IN_STORE_PICKUP,
            self::TYPE_RATE_SHOPPING
        ];

        if (!in_array($type, $allowedTypes)) {
            throw new \InvalidArgumentException('Invalid type ' . $type);
        }

        try {
            $response = $this->httpClient->get(
                $this->getAPIUrl($storeId) . '/shipping-options/' . $type
            );
            $response = \Zend_Json::decode($response);
        } catch (\Throwable $e) {
            $response = [];
        }

        return $response;
    }
}
