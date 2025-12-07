<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Update;

use Calcurates\ModuleMagento\Client\Http\HttpClient;
use Calcurates\ModuleMagento\Client\Http\HttpClientFactory;
use Magento\Framework\Serialize\Serializer\Json;

class PackageInfoLoader
{
    public const BASE_URL = 'https://packagist.org/packages/{PACKAGE}.json';
    public const TIMEOUT = 10;

    /**
     * @var HttpClient
     */
    private $httpClientFactory;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var PackageInfoCache
     */
    private $packageInfoCache;

    /**
     * PackageInfoLoader constructor.
     * @param HttpClientFactory $httpClientFactory
     * @param Json $jsonSerializer
     * @param PackageInfoCache $packageInfoCache
     */
    public function __construct(
        HttpClientFactory $httpClientFactory,
        Json $jsonSerializer,
        PackageInfoCache $packageInfoCache
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->packageInfoCache = $packageInfoCache;
    }

    /**
     * @param string $package
     * @return array|null
     */
    public function getPackageData(string $package): ?array
    {
        $packageData = $this->packageInfoCache->getCachedData($package);
        if (!$packageData) {
            $url = str_replace('{PACKAGE}', $package, static::BASE_URL);

            try {
                $httpClient = $this->httpClientFactory->create();
                $httpClient->setTimeout(static::TIMEOUT);
                $packageData = $this->jsonSerializer->unserialize($httpClient->get($url));
                $this->packageInfoCache->saveCachedData($package, $packageData);
            } catch (\Throwable $e) {
                $packageData = null;
            }
        }

        return $packageData;
    }
}
