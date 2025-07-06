<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data\Order;

interface OrderDataInterface
{
    /**
     * @return string
     */
    public function getDeliveryDate(): string;

    /**
     * @param string $deliveryDate
     * @return void
     */
    public function setDeliveryDate(string $deliveryDate): void;

    /**
     * @return string
     */
    public function getDeliveryDateLabel(): string;

    /**
     * @param string $deliveryDateLabel
     * @return void
     */
    public function setDeliveryDateLabel(string $deliveryDateLabel): void;

    /**
     * @return string
     */
    public function getDeliveryDateTimeFrom(): string;

    /**
     * @param string $timeFrom
     * @return void
     */
    public function setDeliveryDateTimeFrom(string $timeFrom): void;

    /**
     * @return string
     */
    public function getDeliveryDateTimeTo(): string;

    /**
     * @param string $timeTo
     * @return void
     */
    public function setDeliveryDateTimeTo(string $timeTo): void;

    /**
     * @return string
     */
    public function getDeliveryDateTimeLabel(): string;

    /**
     * @param string $timeLabel
     * @return void
     */
    public function setDeliveryDateTimeLabel(string $timeLabel): void;

    /**
     * @return float
     */
    public function getBaseDeliveryDateFeeAmount(): float;

    /**
     * @param float $baseFeeAmount
     * @return void
     */
    public function setBaseDeliveryDateFeeAmount(float $baseFeeAmount): void;

    /**
     * @return float
     */
    public function getDeliveryDateFeeAmount(): float;

    /**
     * @param float $feeAmount
     * @return void
     */
    public function setDeliveryDateFeeAmount(float $feeAmount): void;
}
