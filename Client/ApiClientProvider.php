<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client;

use Calcurates\ModuleMagento\Client\Http\HttpClientFactory;
use Calcurates\ModuleMagento\Client\Http\HttpClient;
use Calcurates\ModuleMagento\Model\Config;

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

    public function __construct(
        HttpClientFactory $httpClientFactory,
        Config $calcuratesConfig
    ) {
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
            $composerPackage = $this->calcuratesConfig->getComposerPackage();
            $client = $this->httpClientFactory->create();
            $client->addHeader(
                'User-Agent',
                $composerPackage->getName() . '/' . $composerPackage->getVersion()
            );
            $client->addHeader('X-API-Key', $this->calcuratesConfig->getCalcuratesToken());

            $this->httpClients[$storeId] = $client;
        }

        return $this->httpClients[$storeId];
    }

    /**
     * @TODO: move inside http client provider
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return string
     */
    public function getApiUrl($storeId)
    {
        return rtrim($this->calcuratesConfig->getApiUrl($storeId), '/') . '/api/magento2';
    }
}
