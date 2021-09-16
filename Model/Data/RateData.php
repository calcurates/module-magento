<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Data;

use Calcurates\ModuleMagento\Api\Data\MetadataInterface;
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

    /**
     * @return string|null
     */
    public function getImageUrl(): ?string
    {
        return $this->_get(self::IMAGE_URL);
    }

    /**
     * @param string $imageUrl
     */
    public function setImageUrl(string $imageUrl): void
    {
        $this->setData(self::IMAGE_URL, $imageUrl);
    }

    /**
     * @return \Calcurates\ModuleMagento\Api\Data\DeliveryDate\DateInterface[]
     */
    public function getDeliveryDatesList(): ?array
    {
        return $this->_get(self::DELIVERY_DATES_LIST);
    }

    /**
     * @param \Calcurates\ModuleMagento\Api\Data\DeliveryDate\DateInterface[] $deliveryDatesList
     * @return void
     */
    public function setDeliveryDatesList(array $deliveryDatesList): void
    {
        $this->setData(self::DELIVERY_DATES_LIST, $deliveryDatesList);
    }

    /**
     * @return \Calcurates\ModuleMagento\Api\Data\MetadataInterface|null
     */
    public function getMetadata(): ?\Calcurates\ModuleMagento\Api\Data\MetadataInterface
    {
        return $this->_get(self::METADATA);
    }

    /**
     * @param \Calcurates\ModuleMagento\Api\Data\MetadataInterface $metadata
     */
    public function setMetadata(\Calcurates\ModuleMagento\Api\Data\MetadataInterface $metadata): void
    {
        $this->setData(self::METADATA, $metadata);
    }
}
