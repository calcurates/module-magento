<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data\DeliveryDate;

interface TimeIntervalInterface
{
    public const ID = 'identifier';
    public const LABEL = 'label';
    public const FEE_AMOUNT = 'fee_amount';
    public const FEE_AMOUNT_INCL_TAX = 'fee_amount_incl_tax';
    public const FEE_AMOUNT_EXCL_TAX = 'fee_amount_excl_tax';
    public const FROM = 'from';
    public const TO = 'to';
    public const INTERVAL_FORMATTED = 'interval_formatted';

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
    public function getLabel(): string;

    /**
     * @param string $label
     * @return void
     */
    public function setLabel(string $label): void;

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
     * @return string
     */
    public function getFrom(): string;

    /**
     * @param string $from
     * @return void
     */
    public function setFrom(string $from): void;

    /**
     * @return string
     */
    public function getTo(): string;

    /**
     * @param string $to
     * @return void
     */
    public function setTo(string $to): void;

    /**
     * @return string
     */
    public function getIntervalFormatted(): string;

    /**
     * @param string $intervalFormatted
     * @return void
     */
    public function setIntervalFormatted(string $intervalFormatted): void;
}
