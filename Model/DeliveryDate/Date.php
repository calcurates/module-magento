<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\DeliveryDate;

use Calcurates\ModuleMagento\Api\Data\DeliveryDate\DateInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class Date extends AbstractSimpleObject implements DateInterface
{
    public function getId(): string
    {
        return (string)$this->_get(self::ID);
    }

    public function setId(string $id): void
    {
        $this->setData(self::ID, $id);
    }

    public function getDate(): string
    {
        return (string)$this->_get(self::DATE);
    }

    public function setDate(string $date): void
    {
        $this->setData(self::DATE, $date);
    }

    public function getDateFormatted(): string
    {
        return (string)$this->_get(self::DATE_FORMATTED);
    }

    public function setDateFormatted(string $dateFormatted): void
    {
        $this->setData(self::DATE_FORMATTED, $dateFormatted);
    }

    public function getFeeAmount(): float
    {
        return (float)$this->_get(self::FEE_AMOUNT);
    }

    public function setFeeAmount(float $feeAmount): void
    {
        $this->setData(self::FEE_AMOUNT, $feeAmount);
    }

    public function getTimeIntervals(): array
    {
        return $this->_get(self::TIME_INTERVALS) ?? [];
    }

    public function setTimeIntervals(array $timeIntervals): void
    {
        $this->setData(self::TIME_INTERVALS, $timeIntervals);
    }
}
