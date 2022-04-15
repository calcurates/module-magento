<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

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

    public function getDeliveryDates(): array
    {
        $deliveryDates = $this->getData(self::DELIVERY_DATES);
        if ($deliveryDates) {
            if (!is_array($deliveryDates)) {
                $deliveryDates = [];
            }
            return $deliveryDates;
        }

        return [];
    }

    public function setDeliveryDates(array $deliveryDates): void
    {
        $this->setData(self::DELIVERY_DATES, $deliveryDates);
    }

    /**
     * @return array
     */
    public function getSplitShipments(): array
    {
        $splitShipments = $this->getData(self::SPLIT_SHIPMENTS);
        if ($splitShipments) {
            if (!is_array($splitShipments)) {
                $splitShipments = [];
            }
            return $splitShipments;
        }

        return [];
    }

    /**
     * @param array $shipments
     * @return void
     */
    public function setSplitShipments(array $shipments): void
    {
        $this->setData(self::SPLIT_SHIPMENTS, $shipments);
    }
}
