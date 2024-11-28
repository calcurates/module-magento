<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\ViewModel;

use Calcurates\ModuleMagento\Api\Data\ShippingLabelInterface;
use Calcurates\ModuleMagento\Api\ShippingLabelRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ShippingLabel implements ArgumentInterface
{
    /**
     * @var ShippingLabelRepositoryInterface
     */
    private $shippingLabelRepository;

    public function __construct(ShippingLabelRepositoryInterface $shippingLabelRepository)
    {
        $this->shippingLabelRepository = $shippingLabelRepository;
    }

    /**
     * @param int $shipmentId
     * @return ShippingLabelInterface|null
     */
    public function getLastShippingLabel(int $shipmentId): ?ShippingLabelInterface
    {
        try {
            return $this->shippingLabelRepository->getLastByShipmentId($shipmentId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
