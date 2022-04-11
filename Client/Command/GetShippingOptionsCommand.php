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
use Calcurates\ModuleMagento\Client\Http\ApiException;
use Psr\Log\LoggerInterface;

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

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GetShippingOptionsCommand constructor.
     * @param ApiClientProvider $apiClientProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        ApiClientProvider $apiClientProvider,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->apiClientProvider = $apiClientProvider;
    }

    /**
     * @param int $storeId
     * @param string|null $type
     * @return array
     * @throws \Zend_Json_Exception
     */
    public function get(int $storeId, ?string $type = null): array
    {
        $httpClient = $this->apiClientProvider->getClient($storeId);
        $apiUrl = $this->apiClientProvider->getApiUrl();
        $requestPath = $apiUrl . '/shipping-options';

        if ($type) {
            $allowedTypes = [
                self::TYPE_CARRIERS,
                self::TYPE_TABLE_RATES,
                self::TYPE_FREE_SHIPPING,
                self::TYPE_FLAT_RATES,
                self::TYPE_IN_STORE_PICKUP,
                self::TYPE_RATE_SHOPPING,
            ];

            if (!in_array($type, $allowedTypes, true)) {
                throw new \InvalidArgumentException('Invalid type ' . $type);
            }

            $requestPath .= '/' . $type;
        }
        try {
            $response = $httpClient->get($requestPath);
        } catch (ApiException $exception) {
            $this->logger->debug(
                var_export(['error' => $exception->getMessage(), 'code' => $exception->getCode()], true)
            );
            return [];
        }

        return \Zend_Json::decode($response);
    }
}
