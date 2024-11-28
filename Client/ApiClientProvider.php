<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client;

use Calcurates\ModuleMagento\Client\Http\HttpClient;
use Calcurates\ModuleMagento\Client\Http\HttpClientFactory;
use Calcurates\ModuleMagento\Model\Config;
use Magento\Framework\App\ProductMetadataInterface;

class ApiClientProvider
{
    private $httpClients = [];

    /**
     * @var HttpClientFactory
     */
    private $httpClientFactory;

    /**
     * @var Config
     */
    private $calcuratesConfig;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        HttpClientFactory $httpClientFactory,
        Config $calcuratesConfig,
        ProductMetadataInterface $productMetadata
    ) {
        $this->productMetadata = $productMetadata;
        $this->httpClientFactory = $httpClientFactory;
        $this->calcuratesConfig = $calcuratesConfig;
    }

    /**
     * @param int $storeId
     * @return HttpClient
     */
    public function getClient(int $storeId): HttpClient
    {
        if (!isset($this->httpClients[$storeId])) {
            $composerData = $this->calcuratesConfig->getComposerData();
            $client = $this->httpClientFactory->create();
            $client->addHeader(
                'User-Agent',
                $composerData['name'] . '/' . $composerData['version']
            );
            $client->addHeader('X-API-Key', $this->calcuratesConfig->getCalcuratesToken($storeId));
            $client->addHeader(
                'X-Magento-Version',
                sprintf(
                    'Magento-%1$s-%2$s',
                    $this->productMetadata->getEdition(),
                    $this->productMetadata->getVersion()
                )
            );

            $this->httpClients[$storeId] = $client;
        }

        return $this->httpClients[$storeId];
    }

    /**
     * @TODO: move inside http client provider
     * @return string
     */
    public function getApiUrl()
    {
        return rtrim($this->calcuratesConfig->getApiUrl(), '/') . '/api/magento2';
    }
}
