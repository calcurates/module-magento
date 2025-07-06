<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */
declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Data\Order\DeliveryDate;

use Magento\Framework\Api\AbstractSimpleObject;
use Calcurates\ModuleMagento\Api\Data\Order\DeliveryDate\CutOffTimeInterface;

class CutOffTime extends AbstractSimpleObject implements CutOffTimeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getHour(): int
    {
        return $this->_get(self::HOUR);
    }

    /**
     * {@inheritdoc}
     */
    public function setHour(int $hour): void
    {
        $this->setData(self::HOUR, $hour);
    }

    /**
     * {@inheritdoc}
     */
    public function getMinute(): int
    {
        return $this->_get(self::MINUTE);
    }

    /**
     * {@inheritdoc}
     */
    public function setMinute(int $minute): void
    {
        $this->setData(self::MINUTE, $minute);
    }
}
