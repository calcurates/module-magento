<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client;

use Calcurates\ModuleMagento\Client\Http\HttpClient;
use Calcurates\ModuleMagento\Model\Config;
use Magento\Framework\Exception\LocalizedException;

class CalcuratesClient
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
    protected function init()
    {
        $composerPackage = $this->calcuratesConfig->getComposerPackage();
        $this->httpClient->addHeader('User-Agent', $composerPackage->getName() . '/' . $composerPackage->getVersion());
        $this->httpClient->addHeader('X-API-Key', $this->calcuratesConfig->getCalcuratesToken());
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
     */
    public function getRates($request, $storeId)
    {
        try {
            $response = $this->httpClient->post(
                $this->getAPIUrl($storeId) . '/rates/magento2',
                \Zend_Json::encode($request)
            );
            $response = \Zend_Json::decode($response);
        } catch (\Throwable $e) {
            throw new LocalizedException(__('Cannot getting labels with API Calcurates %1', $e->getMessage()));
        }

        return $response;
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return string
     */
    protected function getApiUrl($storeId)
    {
        return rtrim($this->calcuratesConfig->getApiUrl($storeId), '/') . '/api/v1';
    }
}
