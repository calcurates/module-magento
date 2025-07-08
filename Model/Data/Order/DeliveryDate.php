<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */
declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Data\Order;

use Calcurates\ModuleMagento\Api\Data\Order\DeliveryDateInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Calcurates\ModuleMagento\Api\Data\Order\DeliveryDate\CutOffTimeInterface;

class DeliveryDate extends AbstractSimpleObject implements DeliveryDateInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFrom(): ?string
    {
        return $this->_get(self::FROM);
    }

    /**
     * {@inheritdoc}
     */
    public function setFrom(?string $from): void
    {
        $this->setData(self::FROM, $from);
    }

    /**
     * {@inheritdoc}
     */
    public function getTo(): ?string
    {
        return $this->_get(self::TO);
    }

    /**
     * {@inheritdoc}
     */
    public function setTo(?string $to): void
    {
        $this->setData(self::TO, $to);
    }

    /**
     * {@inheritdoc}
     */
    public function getCutOffTime(): ?CutOffTimeInterface
    {
        return $this->_get(self::CUTOFF_TIME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCutOffTime(?CutOffTimeInterface $cutOff): void
    {
        $this->setData(self::CUTOFF_TIME, $cutOff);
    }
}
