<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Estimate;

use Calcurates\ModuleMagento\Api\Data\SimpleRateInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class SimpleRate extends AbstractSimpleObject implements SimpleRateInterface
{
    public function getRenderedTemplate(): string
    {
        return $this->_get(self::RENDERED_TEMPLATE);
    }

    public function setRenderedTemplate(string $renderedTemplate): void
    {
        $this->setData(self::RENDERED_TEMPLATE, $renderedTemplate);
    }

    public function getName(): string
    {
        return $this->_get(self::NAME);
    }

    public function setName(string $name): void
    {
        $this->setData(self::NAME, $name);
    }

    public function getAmount(): string
    {
        return $this->_get(self::AMOUNT);
    }

    public function setAmount(string $amount): void
    {
        $this->setData(self::AMOUNT, $amount);
    }

    public function getDeliveryDateFrom(): string
    {
        return $this->_get(self::DELIVERY_DATE_FROM);
    }

    public function setDeliveryDateFrom(string $deliveryDateFrom): void
    {
        $this->setData(self::DELIVERY_DATE_FROM, $deliveryDateFrom);
    }

    public function getDeliveryDateTo(): string
    {
        return $this->_get(self::DELIVERY_DATE_TO);
    }

    public function setDeliveryDateTo(string $deliveryDateTo): void
    {
        $this->setData(self::DELIVERY_DATE_TO, $deliveryDateTo);
    }

    public function getTemplate(): string
    {
        return $this->_get(self::TEMPLATE);
    }

    public function setTemplate(string $template): void
    {
        $this->setData(self::TEMPLATE, $template);
    }

    public function getType(): string
    {
        return $this->_get(self::TYPE);
    }

    public function setType(string $type): void
    {
        $this->setData(self::TYPE, $type);
    }

    public function getCutOffTimeHour()
    {
        return $this->_get(self::CUT_OFF_TIME_HOUR);
    }

    public function setCutOffTimeHour(int $hour): void
    {
        $this->setData(self::CUT_OFF_TIME_HOUR, $hour);
    }

    public function getCutOffTimeMinute()
    {
        return $this->_get(self::CUT_OFF_TIME_MINUTE);
    }

    public function setCutOffTimeMinute(int $minute): void
    {
        $this->setData(self::CUT_OFF_TIME_MINUTE, $minute);
    }
}
