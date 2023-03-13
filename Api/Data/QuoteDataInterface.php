<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data;

interface QuoteDataInterface
{
    public const ID = 'id';
    public const QUOTE_ID = 'quote_id';
    public const DELIVERY_DATE = 'delivery_date_date';
    public const DELIVERY_DATE_LABEL = 'delivery_date_date_label';
    public const DELIVERY_DATE_FEE = 'delivery_date_fee';
    public const DELIVERY_DATE_TIME_FROM = 'delivery_date_time_from';
    public const DELIVERY_DATE_TIME_TO = 'delivery_date_time_to';
    public const DELIVERY_DATE_TIME_LABEL = 'delivery_date_time_label';
    public const DELIVERY_DATE_TIME_FEE = 'delivery_date_time_fee';
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
    public function getQuoteId(): int;

    /**
     * @param int $quoteId
     * @return void
     */
    public function setQuoteId(int $quoteId): void;

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
     * @return float
     */
    public function getDeliveryDateFee(): float;

    /**
     * @param float $deliveryDateFee
     * @return void
     */
    public function setDeliveryDateFee(float $deliveryDateFee): void;

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
    public function getDeliveryDateTimeFee(): float;

    /**
     * @param float $timeFee
     * @return void
     */
    public function setDeliveryDateTimeFee(float $timeFee): void;

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
