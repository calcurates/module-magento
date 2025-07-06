<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data\Order\DeliveryDate;

interface CutOffTimeInterface
{
    public const HOUR = 'hour';
    public const MINUTE = 'minute';

    /**
     * @return int
     */
    public function getHour(): int;

    /**
     * @param int $hour
     * @return void
     */
    public function setHour(int $hour): void;

    /**
     * @return int
     */
    public function getMinute(): int;

    /**
     * @param int $minute
     * @return void
     */
    public function setMinute(int $minute): void;
}
