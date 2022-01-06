<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

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
     * @param string $websiteCode
     * @return array
     */
    public function execute(array $skus, string $websiteCode): array
    {
        $cacheKey = $this->encryptor->hash(json_encode($skus) . '___' . $websiteCode);

        if (!array_key_exists($cacheKey, $this->cache)) {
            $this->cache[$cacheKey] = $this->resource->execute($skus, $websiteCode);
        }

        return $this->cache[$cacheKey];
    }
}
