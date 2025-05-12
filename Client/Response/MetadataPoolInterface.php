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

interface MetadataPoolInterface
{
    /**
     * @return array
     */
    public function getMetadataTypes(): array;

    /**
     * @param string|null $entityType
     * @return array|null
     * @throws LocalizedException
     */
    public function getMetadata(?string $entityType = null): ?array;

    /**
     * @param string $entityType
     * @param object $entity
     * @throws LocalizedException
     */
    public function setMetadata(string $entityType, $entity): void;

    /**
     * @param string $entity
     * @return HydratorInterface
     * @throws LocalizedException
     */
    public function getHydrator(string $entity): HydratorInterface;
}
