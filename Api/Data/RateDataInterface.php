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
    public const TOOLTIP_MESSAGE = 'tooltip_message';
    public const MAP_LINK = 'map_link';
    public const IMAGE_URL = 'image_url';
    public const DELIVERY_DATES_LIST = 'delivery_dates_list';

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

    /**
     * @return string|null
     */
    public function getImageUrl(): ?string;

    /**
     * @param string $imageUrl
     * @return void
     */
    public function setImageUrl(string $imageUrl): void;

    /**
     * @return \Calcurates\ModuleMagento\Api\Data\DeliveryDate\DateInterface[]
     */
    public function getDeliveryDatesList(): ?array;

    /**
     * @param \Calcurates\ModuleMagento\Api\Data\DeliveryDate\DateInterface[] $deliveryDatesList
     * @return void
     */
    public function setDeliveryDatesList(array $deliveryDatesList): void;
}
