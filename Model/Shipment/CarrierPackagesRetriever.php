<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Shipment;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\Data\OrderInterface;

class CarrierPackagesRetriever
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param OrderInterface $order
     * @return array
     */
    public function retrievePackages(OrderInterface $order): array
    {
        $packages = $order->getData(CustomSalesAttributesInterface::CARRIER_PACKAGES);
        if (!$packages) {
            return [];
        }

        $packages = $this->serializer->unserialize($packages);

        foreach ($packages as &$package) {
            $package['id'] = $package['id'] ?? $package['code'];
        }

        return $packages;
    }
}
