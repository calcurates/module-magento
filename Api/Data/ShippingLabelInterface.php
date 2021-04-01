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
    const ID = 'id';
    const SHIPMENT_ID = 'shipment_id';
    const SHIPPING_CARRIER_ID = 'shipping_carrier_id';
    const SHIPPING_SERVICE_ID = 'shipping_service_id';
    const SHIPPING_CARRIER_LABEL = 'shipping_carrier_label';
    const SHIPPING_SERVICE_LABEL = 'shipping_service_label';
    const TRACKING_NUMBER = 'tracking_number';
    const LABEL_CONTENT = 'label_content';
    const LABEL_DATA = 'label_data';
    const PACKAGES = 'packages';
    const CREATED_AT = 'created_at';
    const CARRIER_CODE = 'carrier_code';
    const CARRIER_PROVIDER_CODE = 'carrier_provider_code';

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
     * @return string
     */
    public function getLabelData(): string;

    /**
     * @param string $labelData
     */
    public function setLabelData(string $labelData): void;

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
}
