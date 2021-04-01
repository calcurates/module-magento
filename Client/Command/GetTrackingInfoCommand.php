<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Command;

use Calcurates\ModuleMagento\Client\ApiClientProvider;

class GetTrackingInfoCommand
{
    /**
     * @var ApiClientProvider
     */
    private $apiClientProvider;

    public function __construct(ApiClientProvider $apiClientProvider)
    {
        $this->apiClientProvider = $apiClientProvider;
    }

    /**
     * @param string $carrierCode
     * @param string $providerCode
     * @param string $trackingNumber
     * @param int $storeId
     * @return array
     */
    public function get(string $carrierCode, string $providerCode, string $trackingNumber, int $storeId): array
    {
        $httpClient = $this->apiClientProvider->getClient($storeId);
        $apiUrl = $this->apiClientProvider->getApiUrl($storeId);

        $query = http_build_query([
            'carrierCode' => $carrierCode,
            'providerCode' => $providerCode,
            'trackingNumber' => $trackingNumber
        ]);
        $response = $httpClient->get($apiUrl . '/tracking?' . $query);

        return \Zend_Json::decode($response);
    }
}
