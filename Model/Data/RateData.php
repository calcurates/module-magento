<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Data;

use Calcurates\ModuleMagento\Api\Data\RateDataInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class RateData extends AbstractSimpleObject implements RateDataInterface
{
    /**
     * @return string|null
     */
    public function getTooltipMessage(): ?string
    {
        return $this->_get(self::TOOLTIP_MESSAGE);
    }

    /**
     * @param string $tooltipMessage
     */
    public function setTooltipMessage(string $tooltipMessage): void
    {
        $this->setData(self::TOOLTIP_MESSAGE, $tooltipMessage);
    }

    /**
     * @return string|null
     */
    public function getMapLink(): ?string
    {
        return $this->_get(self::MAP_LINK);
    }

    /**
     * @param string $mapLink
     */
    public function setMapLink(string $mapLink): void
    {
        $this->setData(self::MAP_LINK, $mapLink);
    }
}
