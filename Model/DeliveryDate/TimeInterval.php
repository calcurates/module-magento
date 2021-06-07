<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\DeliveryDate;

use Calcurates\ModuleMagento\Api\Data\DeliveryDate\TimeIntervalInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class TimeInterval extends AbstractSimpleObject implements TimeIntervalInterface
{
    public function getId(): string
    {
        return (string)$this->_get(self::ID);
    }

    public function setId(string $id): void
    {
        $this->setData(self::ID, $id);
    }

    public function getFeeAmount(): float
    {
        return (float)$this->_get(self::FEE_AMOUNT);
    }

    public function setFeeAmount(float $feeAmount): void
    {
        $this->setData(self::FEE_AMOUNT, $feeAmount);
    }

    public function getFrom(): string
    {
        return (string)$this->_get(self::FROM);
    }

    public function setFrom(string $from): void
    {
        $this->setData(self::FROM, $from);
    }

    public function getTo(): string
    {
        return (string)$this->_get(self::TO);
    }

    public function setTo(string $to): void
    {
        $this->setData(self::TO, $to);
    }
}
