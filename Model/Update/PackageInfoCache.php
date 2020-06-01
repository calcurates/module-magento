<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Update;

use Calcurates\ModuleMagento\Api\CalcuratesCacheInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;

class PackageInfoCache
{
    const TYPE_IDENTIFIER = CalcuratesCacheInterface::TYPE_IDENTIFIER;
    const CACHE_TAG = 'calcurates_package_info';
    const PREFIX = 'package';

    /**
     * Cache lifetime is one day
     */
    const LIFETIME = 86400;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * Cache constructor.
     * @param SerializerInterface $serializer
     * @param CacheInterface $cache
     */
    public function __construct(SerializerInterface $serializer, CacheInterface $cache)
    {
        $this->serializer = $serializer;
        $this->cache = $cache;
    }

    /**
     * @param string $package
     * @return array|bool
     */
    public function getCachedData($package)
    {
        $cacheKey = $this->getCacheKey($package);
        $data = $this->cache->load($cacheKey);
        if ($data) {
            $data = $this->serializer->unserialize($data);
        }

        return $data;
    }

    /**
     * @param string $package
     * @param array $arrayData
     * @return bool
     */
    public function saveCachedData($package, $arrayData)
    {
        $cacheKey = $this->getCacheKey($package);
        $data = $this->serializer->serialize($arrayData);

        return $this->cache->save(
            $data,
            $cacheKey,
            [static::CACHE_TAG],
            static::LIFETIME
        );
    }

    /**
     * @param string $key
     * @return string
     */
    private function getCacheKey($key)
    {
        return static::TYPE_IDENTIFIER . '_' . static::PREFIX . '_' . $key;
    }

}
