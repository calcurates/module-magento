<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model;

use Calcurates\ModuleMagento\Api\Data\OrderDataInterface;

class OrderData extends \Magento\Framework\Model\AbstractModel implements OrderDataInterface
{
    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init(\Calcurates\ModuleMagento\Model\ResourceModel\OrderData::class);
    }

    public function getOrderId(): int
    {
        return (int)$this->getData(self::ORDER_ID);
    }

    public function setOrderId(int $orderId): void
    {
        $this->setData(self::ORDER_ID, $orderId);
    }

    public function getDeliveryDate(): string
    {
        return (string)$this->getData(self::DELIVERY_DATE);
    }

    public function setDeliveryDate(string $deliveryDate): void
    {
        $this->setData(self::DELIVERY_DATE, $deliveryDate);
    }

    public function getDeliveryDateTimeFrom(): string
    {
        return (string)$this->getData(self::DELIVERY_DATE_TIME_FROM);
    }

    public function setDeliveryDateTimeFrom(string $timeFrom): void
    {
        $this->setData(self::DELIVERY_DATE_TIME_FROM, $timeFrom);
    }

    public function getDeliveryDateTimeTo(): string
    {
        return (string)$this->getData(self::DELIVERY_DATE_TIME_TO);
    }

    public function setDeliveryDateTimeTo(string $timeTo): void
    {
        $this->setData(self::DELIVERY_DATE_TIME_TO, $timeTo);
    }

    public function getBaseDeliveryDateFeeAmount(): float
    {
        return (float)$this->getData(self::BASE_DD_FEE_AMOUNT);
    }

    public function setBaseDeliveryDateFeeAmount(float $baseFeeAmount): void
    {
        $this->setData(self::BASE_DD_FEE_AMOUNT, $baseFeeAmount);
    }

    public function getDeliveryDateFeeAmount(): float
    {
        return (float)$this->getData(self::DD_FEE_AMOUNT);
    }

    public function setDeliveryDateFeeAmount(float $feeAmount): void
    {
        $this->setData(self::DD_FEE_AMOUNT, $feeAmount);
    }
}
