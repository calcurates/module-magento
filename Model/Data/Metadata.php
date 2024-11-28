<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Data;

use Calcurates\ModuleMagento\Api\Data\MetadataInterface;
use Magento\Framework\DataObject;

class Metadata extends DataObject implements MetadataInterface
{
    /**
     * @return \Calcurates\ModuleMagento\Api\Data\Metadata\DeliveryDatesMetadataInterface
     */
    public function getDeliveryDatesMetadata(): \Calcurates\ModuleMagento\Api\Data\Metadata\DeliveryDatesMetadataInterface
    {
        return $this->getData(MetadataInterface::DELIVERY_DATES_METADATA);
    }

    /**
     * @param \Calcurates\ModuleMagento\Api\Data\Metadata\DeliveryDatesMetadataInterface $deliveryDatesMetadata
     * @return \Calcurates\ModuleMagento\Api\Data\MetadataInterface
     */
    public function setDeliveryDatesMetadata(
        \Calcurates\ModuleMagento\Api\Data\Metadata\DeliveryDatesMetadataInterface $deliveryDatesMetadata
    ): \Calcurates\ModuleMagento\Api\Data\MetadataInterface {
        return $this->setData(MetadataInterface::DELIVERY_DATES_METADATA, $deliveryDatesMetadata);
    }
}
