<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data;

interface RateDataInterface
{
    const TOOLTIP_MESSAGE = 'tooltip_message';
    const MAP_LINK = 'map_link';

    /**
     * @return string|null
     */
    public function getTooltipMessage(): ?string;

    /**
     * @param string $tooltipMessage
     * @return void
     */
    public function setTooltipMessage(string $tooltipMessage): void;

    /**
     * @return string|null
     */
    public function getMapLink(): ?string;

    /**
     * @param string $mapLink
     * @return void
     */
    public function setMapLink(string $mapLink): void;
}