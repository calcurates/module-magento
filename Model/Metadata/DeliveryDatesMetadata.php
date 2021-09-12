<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Metadata;

use Calcurates\ModuleMagento\Api\Data\Metadata\DeliveryDatesMetadataInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class DeliveryDatesMetadata extends AbstractSimpleObject implements DeliveryDatesMetadataInterface
{
    /**
     * @return bool
     */
    public function getTimeSlotDateRequired(): bool
    {
        return (bool) $this->_get(DeliveryDatesMetadataInterface::TIME_SLOT_DATE_REQUIRED);
    }

    /**
     * @param bool $timeSlotDateRequired
     * @return $this
     */
    public function setTimeSlotDateRequired(bool $timeSlotDateRequired): DeliveryDatesMetadataInterface
    {
        return $this->setData(DeliveryDatesMetadataInterface::TIME_SLOT_DATE_REQUIRED, $timeSlotDateRequired);
    }

    /**
     * @return bool
     */
    public function getTimeSlotTimeRequired(): bool
    {
        return (bool) $this->_get(DeliveryDatesMetadataInterface::TIME_SLOT_TIME_REQUIRED);
    }

    /**
     * @param bool $timeSlotTimeRequired
     * @return $this
     */
    public function setTimeSlotTimeRequired(bool $timeSlotTimeRequired): DeliveryDatesMetadataInterface
    {
        return $this->setData(DeliveryDatesMetadataInterface::TIME_SLOT_TIME_REQUIRED, $timeSlotTimeRequired);
    }
}
