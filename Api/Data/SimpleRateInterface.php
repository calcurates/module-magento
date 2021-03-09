<?php
declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data;

interface SimpleRateInterface
{
    const RENDERED_TEMPLATE = 'rendered_template';
    const NAME = 'name';
    const AMOUNT = 'amount';
    const DELIVERY_DATE_FROM = 'delivery_date_from';
    const DELIVERY_DATE_TO = 'delivery_date_to';
    const TEMPLATE = 'template';
    const TYPE = 'type';

    /**
     * @return string
     */
    public function getRenderedTemplate(): string;

    /**
     * @param string $renderedTemplate
     * @return void
     */
    public function setRenderedTemplate(string $renderedTemplate): void;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name): void;

    /**
     * @return string
     */
    public function getAmount(): string;

    /**
     * @param string $amount
     * @return void
     */
    public function setAmount(string $amount): void;

    /**
     * @return string
     */
    public function getDeliveryDateFrom(): string;

    /**
     * @param string $deliveryDateFrom
     * @return void
     */
    public function setDeliveryDateFrom(string $deliveryDateFrom): void;

    /**
     * @return string
     */
    public function getDeliveryDateTo(): string;

    /**
     * @param string $deliveryDateTo
     * @return void
     */
    public function setDeliveryDateTo(string $deliveryDateTo): void;

    /**
     * @return string
     */
    public function getTemplate(): string;

    /**
     * @param string $template
     * @return void
     */
    public function setTemplate(string $template): void;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     * @return void
     */
    public function setType(string $type): void;

}
