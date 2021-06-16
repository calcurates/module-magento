<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model;

use Calcurates\ModuleMagento\Api\Data\QuoteDataInterface;

class QuoteData extends \Magento\Framework\Model\AbstractModel implements QuoteDataInterface
{
    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init(\Calcurates\ModuleMagento\Model\ResourceModel\QuoteData::class);
    }

    public function getQuoteId(): int
    {
        return (int)$this->getData(self::QUOTE_ID);
    }

    public function setQuoteId(int $quoteId): void
    {
        $this->setData(self::QUOTE_ID, $quoteId);
    }

    public function getDeliveryDate(): string
    {
        return (string)$this->getData(self::DELIVERY_DATE);
    }

    public function setDeliveryDate(string $deliveryDate): void
    {
        $this->setData(self::DELIVERY_DATE, $deliveryDate);
    }

    public function getDeliveryDateFee(): float
    {
        return (float)$this->getData(self::DELIVERY_DATE_FEE);
    }

    public function setDeliveryDateFee(float $deliveryDateFee): void
    {
        $this->setData(self::DELIVERY_DATE_FEE, $deliveryDateFee);
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

    public function getDeliveryDateTimeFee(): float
    {
        return (float)$this->getData(self::DELIVERY_DATE_TIME_FEE);
    }

    public function setDeliveryDateTimeFee(float $timeFee): void
    {
        $this->setData(self::DELIVERY_DATE_TIME_FEE, $timeFee);
    }
}
