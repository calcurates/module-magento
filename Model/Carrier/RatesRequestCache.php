<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Carrier;

use Calcurates\ModuleMagento\Api\CalcuratesCacheInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\CacheInterface;

class RatesRequestCache
{
    public const TYPE_IDENTIFIER = CalcuratesCacheInterface::TYPE_IDENTIFIER;
    public const CACHE_TAG = 'calcurates_rates';
    public const RATES_IDENTIFIER = 'rates';
    public const LIFETIME = 60;

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
     * Cache constructor.
     * @param SerializerInterface $serializer
     * @param CacheInterface $cache
     * @param EncryptorInterface $encryptor
     */
    public function __construct(SerializerInterface $serializer, CacheInterface $cache, EncryptorInterface $encryptor)
    {
        $this->serializer = $serializer;
        $this->cache = $cache;
        $this->encryptor = $encryptor;
    }

    /**
     * @param array $request
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array|bool
     */
    public function getCachedData($request, $storeId)
    {
        $cacheKey = $this->getCacheKey($request, $storeId);
        $data = $this->cache->load($cacheKey);
        if ($data) {
            $data = $this->serializer->unserialize($data);
        }

        return $data;
    }

    /**
     * @param array $request
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @param array $response
     * @return bool
     */
    public function saveCachedData($request, $storeId, $response)
    {
        $cacheKey = $this->getCacheKey($request, $storeId);
        $data = $this->serializer->serialize($response);

        return $this->cache->save(
            $data,
            $cacheKey,
            [static::CACHE_TAG],
            static::LIFETIME
        );
    }

    /**
     * @param array $request
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return string
     */
    private function getCacheKey($request, $storeId)
    {
        $storeId = $this->getScalarStoreId($storeId);

        $request['__store_id__'] = $storeId;
        $serializedRequest = $this->serializer->serialize($request);
        $cacheKey = $this->encryptor->hash($serializedRequest);

        return static::TYPE_IDENTIFIER . '_' . static::RATES_IDENTIFIER . '_' . $cacheKey;
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
