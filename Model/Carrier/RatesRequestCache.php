<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Carrier;

use Calcurates\ModuleMagento\Api\CalcuratesCacheInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Calcurates\ModuleMagento\Model\Config;

class RatesRequestCache
{
    public const TYPE_IDENTIFIER = CalcuratesCacheInterface::TYPE_IDENTIFIER;
    public const CACHE_TAG = 'calcurates_rates';
    public const RATES_IDENTIFIER = 'rates';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var Config
     */
    private $config;

    /**
     * RatesRequestCache constructor.
     * @param SerializerInterface $serializer
     * @param CacheInterface $cache
     * @param EncryptorInterface $encryptor
     * @param Config $config
     */
    public function __construct(
        SerializerInterface $serializer,
        CacheInterface $cache,
        EncryptorInterface $encryptor,
        Config $config
    ) {
        $this->serializer = $serializer;
        $this->cache = $cache;
        $this->encryptor = $encryptor;
        $this->config = $config;
    }

    /**
     * @param array $request
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array|null
     */
    public function getCachedData(array $request, $storeId): ?array
    {
        $cacheKey = $this->getCacheKey($request, $storeId);
        $data = $this->cache->load($cacheKey);
        if ($data) {
            $data = $this->serializer->unserialize($data);
            if (!is_array($data)) {
                return null;
            }
            return $data;
        }

        return null;
    }

    /**
     * @param array $request
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @param array $response
     * @return bool
     */
    public function saveCachedData(array $request, $storeId, array $response): bool
    {
        $cacheKey = $this->getCacheKey($request, $storeId);
        $data = $this->serializer->serialize($response);

        return $this->cache->save(
            $data,
            $cacheKey,
            [static::CACHE_TAG],
            $this->config->getRateRequestCacheTimeout($storeId)
        );
    }

    /**
     * @param array $request
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return string
     */
    private function getCacheKey(array $request, $storeId): string
    {
        $storeId = $this->getScalarStoreId($storeId);
        $cacheableRequest = $this->removeEmptyFieldsFromRequest($request);
        $cacheableRequest['__store_id__'] = $storeId;
        $serializedRequest = $this->serializer->serialize($cacheableRequest);
        $cacheKey = $this->encryptor->hash($serializedRequest);

        return static::TYPE_IDENTIFIER . '_' . static::RATES_IDENTIFIER . '_' . $cacheKey;
    }

    /**
     * Removes empty fields from cache key request
     * @param array $requestData
     * @return array
     */
    private function removeEmptyFieldsFromRequest(array $requestData): array
    {
        $result = [];
        foreach ($requestData as $fieldName => $data) {
            if ($fieldName === 'estimate') {
                continue;
            }
            if (is_array($data)) {
                $result[$fieldName] = $this->removeEmptyFieldsFromRequest($data);
            } elseif ((bool) $data) {
                $result[$fieldName] = $data;
            }
        }
        return $result;
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return int|string
     */
    private function getScalarStoreId($storeId)
    {
        if (is_object($storeId)) {
            $storeId = $storeId->getId();
        }

        return $storeId;
    }
}
