<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data\DeliveryDate;

interface DateInterface
{
    public const ID = 'id';
    public const DATE = 'date';
    public const LABEL = 'label';
    public const DATE_FORMATTED = 'date_formatted';
    public const FEE_AMOUNT = 'fee_amount';
    public const FEE_AMOUNT_INCL_TAX = 'fee_amount_incl_tax';
    public const FEE_AMOUNT_EXCL_TAX = 'fee_amount_excl_tax';
    public const TIME_INTERVALS = 'time_intervals';

    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @param string $id
     * @return void
     */
    public function setId(string $id): void;

    /**
     * @return string
     */
    public function getDate(): string;

    /**
     * @param string $date
     * @return void
     */
    public function setDate(string $date): void;

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @param string $label
     * @return void
     */
    public function setLabel(string $label): void;

    /**
     * @return string
     */
    public function getDateFormatted(): string;

    /**
     * @param string $dateFormatted
     * @return void
     */
    public function setDateFormatted(string $dateFormatted): void;

    /**
     * @return float
     */
    public function getFeeAmount(): float;

    /**
     * @param float $feeAmount
     * @return void
     */
    public function setFeeAmount(float $feeAmount): void;

    /**
     * @return float
     */
    public function getFeeAmountInclTax(): float;

    /**
     * @return float
     */
    public function getFeeAmountExclTax(): float;

    /**
     * @param float $feeAmount
     * @return void
     */
    public function setFeeAmountInclTax(float $feeAmount): void;

    /**
     * @param float $feeAmount
     * @return void
     */
    public function setFeeAmountExclTax(float $feeAmount): void;

    /**
     * @return \Calcurates\ModuleMagento\Api\Data\DeliveryDate\TimeIntervalInterface[]
     */
    public function getTimeIntervals(): array;

    /**
     * @param \Calcurates\ModuleMagento\Api\Data\DeliveryDate\TimeIntervalInterface[] $timeIntervals
     * @return void
     */
    public function setTimeIntervals(array $timeIntervals): void;
}
