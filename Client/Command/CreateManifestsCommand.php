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

class CreateManifestsCommand
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
     * @param array $labelIds
     * @param int $storeId
     * @return array
     * @throws \Calcurates\ModuleMagento\Client\Http\ApiException
     */
    public function createManifests(string $carrierCode, string $providerCode, array $labelIds, int $storeId): array
    {
        $httpClient = $this->apiClientProvider->getClient($storeId);
        $apiUrl = $this->apiClientProvider->getApiUrl();

        $query = \Laminas\Json\Json::encode([
            'carrierCode' => $carrierCode,
            'providerCode' => $providerCode,
            'labelsId' => $labelIds
        ]);

        $response = $httpClient->post($apiUrl . '/manifests', $query);

        return \Laminas\Json\Json::decode($response, 1);
    }
}
