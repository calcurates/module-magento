<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data;

interface OrderDataInterface
{
    public const ID = 'id';
    public const ORDER_ID = 'order_id';
    public const DELIVERY_DATE = 'delivery_date_date';
    public const DELIVERY_DATE_TIME_FROM = 'delivery_date_time_from';
    public const DELIVERY_DATE_TIME_TO = 'delivery_date_time_to';
    public const BASE_DD_FEE_AMOUNT = 'base_dd_fee_amount';
    public const DD_FEE_AMOUNT = 'dd_fee_amount';
    public const DELIVERY_DATES = 'delivery_dates';
    public const SPLIT_SHIPMENTS = 'split_shipments';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return int
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getOrderId(): int;

    /**
     * @param int $orderId
     * @return void
     */
    public function setOrderId(int $orderId): void;

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

    /**
     * @return array
     */
    public function getDeliveryDates(): array;

    /**
     * @param array $deliveryDates
     * @return void
     */
    public function setDeliveryDates(array $deliveryDates): void;

    /**
     * @return array
     */
    public function getSplitShipments(): array;

    /**
     * @param array $shipments
     * @return void
     */
    public function setSplitShipments(array $shipments): void;
}
