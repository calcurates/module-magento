<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data\Order\Item;

interface ShippingInformationInterface
{
    public const METHOD = 'method';
    public const TITLE = 'title';
    public const QTY = 'qty';
    public const METHOD_PRICE = 'method_price';
    public const CODE = 'code';

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @param string $method
     * @return void
     */
    public function setMethod(string $method): void;

    /**
     * @return float
     */
    public function getMethodPrice(): float;

    /**
     * @param float $price
     * @return void
     */
    public function setMethodPrice(float $price): void;

    /**
     * @return float
     */
    public function getQty(): float;

    /**
     * @param float $qty
     * @return void
     */
    public function setQty(float $qty): void;

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @param string $title
     * @return void
     */
    public function setTitle(string $title): void;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param string $code
     * @return void
     */
    public function setCode(string $code): void;
}
