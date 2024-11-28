<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data;

interface SimpleRateInterface
{
    public const RENDERED_TEMPLATE = 'rendered_template';
    public const NAME = 'name';
    public const AMOUNT = 'amount';
    public const DELIVERY_DATE_FROM = 'delivery_date_from';
    public const DELIVERY_DATE_TO = 'delivery_date_to';
    public const TEMPLATE = 'template';
    public const TYPE = 'type';
    public const CUT_OFF_TIME_HOUR = 'cut_off_time_hour';
    public const CUT_OFF_TIME_MINUTE = 'cut_off_time_minute';

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

    /**
     * @return int|null
     */
    public function getCutOffTimeHour();

    /**
     * @param int $hour
     * @return void
     */
    public function setCutOffTimeHour(int $hour): void;

    /**
     * @return int|null
     */
    public function getCutOffTimeMinute();

    /**
     * @param int $minute
     * @return void
     */
    public function setCutOffTimeMinute(int $minute): void;
}
