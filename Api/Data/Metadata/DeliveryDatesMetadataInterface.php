<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data\Metadata;

interface DeliveryDatesMetadataInterface
{
    public const TIME_SLOT_DATE_REQUIRED = 'timeSlotDateRequired';
    public const TIME_SLOT_TIME_REQUIRED = 'timeSlotTimeRequired';

    /**
     * @return bool
     */
    public function getTimeSlotDateRequired(): bool;

    /**
     * @param bool $timeSlotDateRequired
     * @return $this
     */
    public function setTimeSlotDateRequired(bool $timeSlotDateRequired): DeliveryDatesMetadataInterface;

    /**
     * @return bool
     */
    public function getTimeSlotTimeRequired(): bool;

    /**
     * @param bool $timeSlotTimeRequired
     * @return $this
     */
    public function setTimeSlotTimeRequired(bool $timeSlotTimeRequired): DeliveryDatesMetadataInterface;
}
