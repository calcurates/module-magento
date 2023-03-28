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
use Laminas\Json\Json;
use Laminas\Json\Exception\RuntimeException;

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
     * @throws ApiException
     * @throws RuntimeException
     */
    public function get(string $carrierCode, string $providerCode, string $trackingNumber, int $storeId): array
    {
        $httpClient = $this->apiClientProvider->getClient($storeId);
        $apiUrl = $this->apiClientProvider->getApiUrl();

        $query = http_build_query([
            'carrierCode' => $carrierCode,
            'providerCode' => $providerCode,
            'trackingNumber' => $trackingNumber
        ]);
        $response = $httpClient->get($apiUrl . '/tracking?' . $query);

        return Json::decode($response, 1);
    }
}
