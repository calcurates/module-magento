<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data;

interface MetadataInterface
{
    public const DELIVERY_DATES_METADATA = 'delivery_dates_metadata';

    /**
     * @return \Calcurates\ModuleMagento\Api\Data\Metadata\DeliveryDatesMetadataInterface
     */
    public function getDeliveryDatesMetadata(): \Calcurates\ModuleMagento\Api\Data\Metadata\DeliveryDatesMetadataInterface;

    /**
     * @param \Calcurates\ModuleMagento\Api\Data\Metadata\DeliveryDatesMetadataInterface $deliveryDatesMetadata
     * @return \Calcurates\ModuleMagento\Api\Data\MetadataInterface
     */
    public function setDeliveryDatesMetadata(
        \Calcurates\ModuleMagento\Api\Data\Metadata\DeliveryDatesMetadataInterface $deliveryDatesMetadata
    ): \Calcurates\ModuleMagento\Api\Data\MetadataInterface;
}
