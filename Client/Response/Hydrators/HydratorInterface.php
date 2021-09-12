<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Hydrators;

interface HydratorInterface
{
    /**
     * @param array $response
     * @return array|null
     */
    public function extract(array $response): ?array;

    /**
     * @param object $entity
     * @param array $data
     * @return object
     */
    public function hydrate(object $entity, array $data): object;
}
