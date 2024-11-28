<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\Data;

interface ShippingLabelInterface
{
    public const ID = 'id';
    public const SHIPMENT_ID = 'shipment_id';
    public const SHIPPING_CARRIER_ID = 'shipping_carrier_id';
    public const SHIPPING_SERVICE_ID = 'shipping_service_id';
    public const SHIPPING_CARRIER_LABEL = 'shipping_carrier_label';
    public const SHIPPING_SERVICE_LABEL = 'shipping_service_label';
    public const TRACKING_NUMBER = 'tracking_number';
    public const LABEL_CONTENT = 'label_content';
    public const LABEL_DATA = 'label_data';
    public const PACKAGES = 'packages';
    public const CREATED_AT = 'created_at';
    public const CARRIER_CODE = 'carrier_code';
    public const CARRIER_PROVIDER_CODE = 'carrier_provider_code';
    public const MANIFEST_ID = 'manifest_id';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int|null $id
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getShipmentId(): int;

    /**
     * @param int $shipmentId
     */
    public function setShipmentId(int $shipmentId): void;

    /**
     * @return string
     */
    public function getShippingCarrierId(): string;

    /**
     * @param string $shippingCarrierId
     */
    public function setShippingCarrierId(string $shippingCarrierId): void;

    /**
     * @return string
     */
    public function getShippingServiceId(): string;

    /**
     * @param string $shippingServiceId
     */
    public function setShippingServiceId(string $shippingServiceId): void;

    /**
     * @return string|null
     */
    public function getShippingCarrierLabel(): ?string;

    /**
     * @param string $shippingCarrierLabel
     */
    public function setShippingCarrierLabel(string $shippingCarrierLabel): void;

    /**
     * @return string|null
     */
    public function getShippingServiceLabel(): ?string;

    /**
     * @param string $shippingServiceLabel
     */
    public function setShippingServiceLabel(string $shippingServiceLabel): void;

    /**
     * @return string|null
     */
    public function getTrackingNumber(): ?string;

    /**
     * @param string $trackingNumber
     */
    public function setTrackingNumber(string $trackingNumber): void;

    /**
     * @return string|null
     */
    public function getLabelContent(): ?string;

    /**
     * @param string $labelContent
     */
    public function setLabelContent(string $labelContent): void;

    /**
     * @return array
     */
    public function getLabelData(): array;

    /**
     * @param array $labelData
     */
    public function setLabelData(array $labelData): void;

    /**
     * @return array
     */
    public function getPackages(): array;

    /**
     * @param array $packages
     */
    public function setPackages(array $packages): void;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @param string $createdAt
     */
    public function setCreatedAt(string $createdAt): void;

    /**
     * Calcurates carrier code
     * @return string|null
     */
    public function getCarrierCode(): ?string;

    /**
     * @param string $carrierCode
     */
    public function setCarrierCode(string $carrierCode): void;

    /**
     * Calcurates Carrier Provider Code
     * @return string|null
     */
    public function getCarrierProviderCode(): ?string;

    /**
     * @param string $carrierProviderCode
     */
    public function setCarrierProviderCode(string $carrierProviderCode): void;

    /**
     * @return int|null
     */
    public function getManifestId(): ?int;

    /**
     * @param int|null $manifestId
     */
    public function setManifestId(?int $manifestId): void;
}
