<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model;

use Calcurates\ModuleMagento\Api\Data\ShippingLabelInterface;

class ShippingLabel extends \Magento\Framework\Model\AbstractModel implements ShippingLabelInterface
{
    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init(\Calcurates\ModuleMagento\Model\ResourceModel\ShippingLabel::class);
    }

    /**
     * @return int
     */
    public function getShipmentId(): int
    {
        return (int)$this->getData(self::SHIPMENT_ID);
    }

    /**
     * @param int $shipmentId
     */
    public function setShipmentId(int $shipmentId): void
    {
        $this->setData(self::SHIPMENT_ID, $shipmentId);
    }

    /**
     * @return string
     */
    public function getShippingCarrierId(): string
    {
        return (string)$this->getData(self::SHIPPING_CARRIER_ID);
    }

    /**
     * @param string $shippingCarrierId
     */
    public function setShippingCarrierId(string $shippingCarrierId): void
    {
        $this->setData(self::SHIPPING_CARRIER_ID, $shippingCarrierId);
    }

    /**
     * @return string
     */
    public function getShippingServiceId(): string
    {
        return (string)$this->getData(self::SHIPPING_SERVICE_ID);
    }

    /**
     * @param string $shippingServiceId
     */
    public function setShippingServiceId(string $shippingServiceId): void
    {
        $this->setData(self::SHIPPING_SERVICE_ID, $shippingServiceId);
    }

    /**
     * @return string|null
     */
    public function getShippingCarrierLabel(): ?string
    {
        return $this->getData(self::SHIPPING_CARRIER_LABEL);
    }

    /**
     * @param string $shippingCarrierLabel
     */
    public function setShippingCarrierLabel(string $shippingCarrierLabel): void
    {
        $this->setData(self::SHIPPING_CARRIER_LABEL, $shippingCarrierLabel);
    }

    /**
     * @return string|null
     */
    public function getShippingServiceLabel(): ?string
    {
        return $this->getData(self::SHIPPING_SERVICE_LABEL);
    }

    /**
     * @param string $shippingServiceLabel
     */
    public function setShippingServiceLabel(string $shippingServiceLabel): void
    {
        $this->setData(self::SHIPPING_SERVICE_LABEL, $shippingServiceLabel);
    }

    /**
     * @return string|null
     */
    public function getTrackingNumber(): ?string
    {
        return $this->getData(self::TRACKING_NUMBER);
    }

    /**
     * @param string $trackingNumber
     */
    public function setTrackingNumber(string $trackingNumber): void
    {
        $this->setData(self::TRACKING_NUMBER, $trackingNumber);
    }

    /**
     * @return string|null
     */
    public function getLabelContent(): ?string
    {
        return $this->getData(self::LABEL_CONTENT);
    }

    /**
     * @param string $labelContent
     */
    public function setLabelContent(string $labelContent): void
    {
        $this->setData(self::LABEL_CONTENT, $labelContent);
    }

    /**
     * @return array
     */
    public function getLabelData(): array
    {
        return $this->getData(self::LABEL_DATA);
    }

    /**
     * @param array $labelData
     */
    public function setLabelData(array $labelData): void
    {
        $this->setData(self::LABEL_DATA, $labelData);
    }

    /**
     * @return array
     */
    public function getPackages(): array
    {
        return $this->getData(self::PACKAGES);
    }

    /**
     * @param array $packages
     */
    public function setPackages(array $packages): void
    {
        $this->setData(self::PACKAGES, $packages);
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return (string)$this->getData(self::CREATED_AT);
    }

    /**
     * @param string $createdAt
     */
    public function setCreatedAt(string $createdAt): void
    {
        $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getCarrierCode(): ?string
    {
        return $this->getData(self::CARRIER_CODE);
    }

    public function setCarrierCode(string $carrierCode): void
    {
        $this->setData(self::CARRIER_CODE, $carrierCode);
    }

    public function getCarrierProviderCode(): ?string
    {
        return $this->getData(self::CARRIER_PROVIDER_CODE);
    }

    public function setCarrierProviderCode(string $carrierProviderCode): void
    {
        $this->setData(self::CARRIER_PROVIDER_CODE, $carrierProviderCode);
    }

    public function getManifestId(): ?int
    {
        $id = $this->getData(self::MANIFEST_ID);

        return $id ? (int)$id : null;
    }

    public function setManifestId(?int $manifestId): void
    {
        $this->setData(self::MANIFEST_ID, $manifestId);
    }
}
