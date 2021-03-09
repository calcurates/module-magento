<?php

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
}
