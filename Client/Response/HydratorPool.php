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

class HydratorPool
{
    /**
     * @var HydratorInterface[]
     */
    private $hydrators;

    /**
     * @param array $hydrators
     */
    public function __construct(
        array $hydrators = []
    ) {
        $this->hydrators = $hydrators;
    }

    /**
     * @param string $entity
     * @return HydratorInterface
     * @throws LocalizedException
     */
    public function getHydrator(string $entity): HydratorInterface
    {
        if (!isset($this->hydrators[$entity])) {
            throw new LocalizedException(__('Unknown entity type: %s requested', $entity));
        }
        return $this->hydrators[$entity];
    }
}
