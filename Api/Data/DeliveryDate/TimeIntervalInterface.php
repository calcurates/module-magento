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
    public const FEE_AMOUNT = 'fee_amount';
    public const FROM = 'from';
    public const TO = 'to';

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
     * @return float
     */
    public function getFeeAmount(): float;

    /**
     * @param float $feeAmount
     * @return void
     */
    public function setFeeAmount(float $feeAmount): void;

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
}
