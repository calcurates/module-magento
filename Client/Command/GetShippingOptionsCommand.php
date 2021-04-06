<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Command;

use Calcurates\ModuleMagento\Client\ApiClientProvider;

class GetShippingOptionsCommand
{
    const TYPE_CARRIERS = 'carriers';
    const TYPE_TABLE_RATES = 'table-rates';
    const TYPE_FLAT_RATES = 'flat-rates';
    const TYPE_FREE_SHIPPING = 'free-shipping';
    const TYPE_IN_STORE_PICKUP = 'in-store-pickups';
    const TYPE_RATE_SHOPPING = 'rate-shopping';

    /**
     * @var ApiClientProvider
     */
    private $apiClientProvider;

    public function __construct(ApiClientProvider $apiClientProvider)
    {
        $this->apiClientProvider = $apiClientProvider;
    }

    /**
     * @param string $type
     * @param int $storeId
     * @return array
     */
    public function get(string $type, int $storeId): array
    {
        $httpClient = $this->apiClientProvider->getClient($storeId);
        $apiUrl = $this->apiClientProvider->getApiUrl($storeId);

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

        $response = $httpClient->get($apiUrl . '/shipping-options/' . $type);

        return \Zend_Json::decode($response);
    }

}
