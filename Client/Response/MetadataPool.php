<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response;

use Calcurates\ModuleMagento\Client\Response\Hydrators\HydratorInterface;
use Magento\Framework\Exception\LocalizedException;

class MetadataPool implements MetadataPoolInterface
{
    /**
     * @var array
     */
    protected $metadata;

    /**
     * @var array
     */
    protected $registry;

    /**
     * @var HydratorPool
     */
    private $hydratorPool;

    /**
     * @param HydratorPool $hydratorPool
     * @param array $metadata
     */
    public function __construct(
        HydratorPool $hydratorPool,
        array $metadata = []
    ) {
        $this->hydratorPool = $hydratorPool;
        $this->metadata = $metadata;
    }

    /**
     * @return array
     */
    public function getMetadataTypes(): array
    {
        return $this->metadata;
    }

    /**
     * @param string|null $entityType
     * @return array|null
     * @throws LocalizedException
     */
    public function getMetadata(string $entityType = null): ?array
    {
        if ($entityType === null) {
            return $this->registry;
        }
        if (!in_array($entityType, $this->metadata, true)) {
            throw new LocalizedException(__('Unknown entity type: %s requested', $entityType));
        }
        return $this->registry[$entityType] ?? null;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @throws LocalizedException
     */
    public function setMetadata(string $entityType, object $entity): void
    {
        if (!in_array($entityType, $this->metadata, true)) {
            throw new LocalizedException(__('Unknown entity type: %s requested', $entityType));
        }
        $this->registry[$entityType] = $entity;
    }

    /**
     * @param string $entity
     * @return HydratorInterface
     * @throws LocalizedException
     */
    public function getHydrator(string $entity): HydratorInterface
    {
        return $this->hydratorPool->getHydrator($entity);
    }
}
