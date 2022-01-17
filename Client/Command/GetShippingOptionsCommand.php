<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Command;

use Calcurates\ModuleMagento\Client\ApiClientProvider;

class GetShippingOptionsCommand
{
    public const TYPE_CARRIERS = 'carriers';
    public const TYPE_TABLE_RATES = 'table-rates';
    public const TYPE_FLAT_RATES = 'flat-rates';
    public const TYPE_FREE_SHIPPING = 'free-shipping';
    public const TYPE_IN_STORE_PICKUP = 'in-store-pickups';
    public const TYPE_RATE_SHOPPING = 'rate-shopping';

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
        $apiUrl = $this->apiClientProvider->getApiUrl();

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
