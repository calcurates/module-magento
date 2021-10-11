<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\InStorePickup;

use Calcurates\ModuleMagento\Api\Data\InStorePickup\PickupLocationInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface PickupLocationRepositoryInterface
{
    /**
     * @param int $shippingOptionId
     * @return PickupLocationInterface
     * @throws NoSuchEntityException
     */
    public function getByShippingOptionId(int $shippingOptionId): PickupLocationInterface;

    /**
     * @param string $code
     * @return PickupLocationInterface
     * @throws NoSuchEntityException
     */
    public function getByCode(string $code): PickupLocationInterface;

    /**
     * @return PickupLocationInterface[]
     */
    public function getList(): array;
}
