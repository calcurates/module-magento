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
use Calcurates\ModuleMagento\Model\Config;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Zend_Json;

class GetCarriersSettingsCommand
{
    /**
     * @var ApiClientProvider
     */
    private $apiClientProvider;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ApiClientProvider $apiClientProvider
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        ApiClientProvider $apiClientProvider,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->apiClientProvider = $apiClientProvider;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     */
    public function get(int $storeId): array
    {
        $httpClient = $this->apiClientProvider->getClient($storeId);
        $apiUrl = $this->apiClientProvider->getApiUrl();

        try {
            $response = $httpClient->get($apiUrl . '/carriers-settings');
            $response = Zend_Json::decode($response);
        } catch (Exception $exception) {
            if ($this->config->isDebug($storeId)) {
                $this->logger->debug(
                    var_export(['error' => $exception->getMessage(), 'code' => $exception->getCode()], true)
                );
            }
            throw new LocalizedException(__('Cannot get Carriers settings with API Calcurates %1', $exception->getMessage()));
        }

        return $response;
    }
}
