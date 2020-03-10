<?php

namespace Calcurates\ModuleMagento\Model\Source;

class GetSourceCodesPerSkus
{
    /**
     * @var \Calcurates\ModuleMagento\Model\ResourceModel\GetSourceCodesPerSkus
     */
    private $resource;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * GetSourceCodesPerSkus constructor.
     * @param \Calcurates\ModuleMagento\Model\ResourceModel\GetSourceCodesPerSkus $resource
     */
    public function __construct(\Calcurates\ModuleMagento\Model\ResourceModel\GetSourceCodesPerSkus $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param array $skus
     * @return array
     */
    public function execute(array $skus)
    {
        $cacheKey = md5(implode('___', $skus));

        if (!array_key_exists($cacheKey, $this->cache)) {
            $this->cache[$cacheKey] = $this->resource->execute($skus);
        }

        return $this->cache[$cacheKey];
    }
}
