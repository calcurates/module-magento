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
use Calcurates\ModuleMagento\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Calcurates\ModuleMagento\Client\ApiClientProvider;

class CalcuratesClient implements CalcuratesClientInterface
{
    /**
     * @var Config
     */
    private $calcuratesConfig;

    /**
     * @var ApiClientProvider
     */
    private $apiClientProvider;

    /**
     * CalcuratesClient constructor.
     * @param Config $calcuratesConfig
     * @param ApiClientProvider $apiClientProvider
     */
    public function __construct(
        Config $calcuratesConfig,
        ApiClientProvider $apiClientProvider
    ) {
        $this->calcuratesConfig = $calcuratesConfig;
        $this->apiClientProvider = $apiClientProvider;
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getShippingCarriersWithServices($storeId)
    {
        $response = $this->getShippingOptions(self::TYPE_CARRIERS, $storeId);

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
     * @param int $storeId
     * @return string
     * @throws LocalizedException
     */
    public function getLabelContent(string $url, int $storeId): string
    {
        try {
            $httpClient = $this->apiClientProvider->getClient($storeId);
            $response = $httpClient->get($url);
        } catch (\Throwable $e) {
            throw new LocalizedException(__('Cannot getting label from API Calcurates %1', $e->getMessage()));
        }

        return $response;
    }

    /**
     * @param string $serviceId
     * @param string $tracking
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     */
    public function getTrackingInfo(string $serviceId, string $tracking, $storeId): array
    {
        try {
            $query = http_build_query([
                'serviceId' => $serviceId,
                'trackingNumber' => $tracking
            ]);
            $httpClient = $this->apiClientProvider->getClient($storeId);
            $apiUrl = $this->apiClientProvider->getApiUrl();
            $response = $httpClient->get($apiUrl . '/tracking?' . $query);
            $response = \Zend_Json::decode($response);
        } catch (\Throwable $e) {
            throw new LocalizedException(__('Cannot getting tracking from API Calcurates %1', $e->getMessage()));
        }

        return $response;
    }

    /**
     * @param array $request
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     */
    public function createShippingLabel(array $request, $storeId): array
    {
        try {
            $httpClient = $this->apiClientProvider->getClient($storeId);
            $apiUrl = $this->apiClientProvider->getApiUrl();
            $response = $httpClient->post(
                $apiUrl . '/labels',
                \Zend_Json::encode($request)
            );
            $response = \Zend_Json::decode($response);
        } catch (\Throwable $e) {
            throw new LocalizedException(__('Cannot create label with API Calcurates %1', $e->getMessage()));
        }

        return $response;
    }

    /**
     * @param array $request
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     * @throws ApiException
     */
    public function getRates(array $request, $storeId): array
    {
        $timeout = $this->calcuratesConfig->getApiGetRatesTimeout($storeId);
        try {
            $httpClient = $this->apiClientProvider->getClient($storeId);
            $apiUrl = $this->apiClientProvider->getApiUrl();
            $httpClient->setTimeout($timeout);
            $response = $httpClient->post(
                $apiUrl . '/rates',
                \Zend_Json::encode($request)
            );
            $httpClient->setTimeout(null);
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
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     * @throws ApiException
     */
    public function getRatesSimple(array $request, $storeId): array
    {
        $timeout = $this->calcuratesConfig->getApiGetRatesTimeout($storeId);
        try {
            $httpClient = $this->apiClientProvider->getClient($storeId);
            $apiUrl = $this->apiClientProvider->getApiUrl();
            $httpClient->setTimeout($timeout);
            $response = $httpClient->post(
                $apiUrl . '/rates-simple',
                \Zend_Json::encode($request)
            );
            $httpClient->setTimeout(null);
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
    public function getRatesSplitCheckout(array $request, $storeId): array
    {
        $timeout = $this->calcuratesConfig->getApiGetRatesTimeout($storeId);
        try {
            $httpClient = $this->apiClientProvider->getClient($storeId);
            $apiUrl = $this->apiClientProvider->getApiUrl();
            $httpClient->setTimeout($timeout);
            $response = $httpClient->post(
                $apiUrl . '/rates-split-checkout',
                \Zend_Json::encode($request)
            );
            $httpClient->setTimeout(null);
            $response = \Zend_Json::decode($response);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new LocalizedException(__('Cannot getting rates with API Calcurates %1', $e->getMessage()));
        }

        return $response;
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getCustomPackages($storeId)
    {
        try {
            $httpClient = $this->apiClientProvider->getClient($storeId);
            $apiUrl = $this->apiClientProvider->getApiUrl();
            $response = $httpClient->get(
                $apiUrl . '/custom-packages'
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
     * @param int $storeId
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
            $httpClient = $this->apiClientProvider->getClient($storeId);
            $apiUrl = $this->apiClientProvider->getApiUrl();
            $response = $httpClient->get(
                $apiUrl . '/shipping-options/' . $type
            );
            $response = \Zend_Json::decode($response);
        } catch (\Throwable $e) {
            $response = [];
        }

        return $response;
    }
}
