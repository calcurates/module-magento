<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data\Order;

interface DeliveryDateInterface
{
    public const FROM = 'from';
    public const TO = 'to';
    public const CUTOFF_TIME = 'cutOffTime';
    public const CUTOFF_DAYS_IN_TRANS_FROM = 'daysInTransitFrom';
    public const CUTOFF_DAYS_IN_TRANS_TO = 'daysInTransitTo';
    public const TIME_SLOTS = 'timeSlots';

    /**
     * @return string|null
     */
    public function getFrom(): ?string;

    /**
     * @param string|null $from
     * @return void
     */
    public function setFrom(?string $from): void;

    /**
     * @return string
     */
    public function getTo(): ?string;

    /**
     * @param string|null $to
     * @return void
     */
    public function setTo(?string $to): void;

    /**
     * @return \Calcurates\ModuleMagento\Api\Data\Order\DeliveryDate\CutOffTimeInterface|null
     */
    public function getCutOffTime(): ?\Calcurates\ModuleMagento\Api\Data\Order\DeliveryDate\CutOffTimeInterface;

    /**
     * @param \Calcurates\ModuleMagento\Api\Data\Order\DeliveryDate\CutOffTimeInterface|null $cutOff
     * @return void
     */
    public function setCutOffTime(
        ?\Calcurates\ModuleMagento\Api\Data\Order\DeliveryDate\CutOffTimeInterface $cutOff
    ): void;
}
