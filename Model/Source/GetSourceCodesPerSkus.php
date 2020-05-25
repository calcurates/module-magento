<?php

namespace Calcurates\ModuleMagento\Model\Source;

use Calcurates\ModuleMagento\Model\ResourceModel\GetSourceCodesPerSkus as GetSourceCodesPerSkusResource;
use Magento\Framework\Encryption\EncryptorInterface;

class GetSourceCodesPerSkus
{
    /**
     * @var GetSourceCodesPerSkusResource
     */
    private $resource;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * GetSourceCodesPerSkus constructor.
     * @param GetSourceCodesPerSkusResource $resource
     * @param EncryptorInterface $encryptor
     */
    public function __construct(GetSourceCodesPerSkusResource $resource, EncryptorInterface $encryptor)
    {
        $this->resource = $resource;
        $this->encryptor = $encryptor;
    }

    /**
     * @param array $skus
     * @return array
     */
    public function execute(array $skus)
    {
        $cacheKey = $this->encryptor->hash(json_encode($skus));

        if (!array_key_exists($cacheKey, $this->cache)) {
            $this->cache[$cacheKey] = $this->resource->execute($skus);
        }

        return $this->cache[$cacheKey];
    }
}
