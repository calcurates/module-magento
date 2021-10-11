<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data\InStorePickup;

interface PickupLocationInterface
{
    const KEY_CODE = 'code';
    const KEY_SHIPPING_OPTION_ID = 'shipping_option_id';
    const KEY_NAME = 'name';
    const KEY_EMAIL = 'email';
    const KEY_FAX = 'fax';
    const KEY_CONTACT_NAME = 'contact_name';
    const KEY_LATITUDE = 'latitude';
    const KEY_LONGITUDE = 'longitude';
    const KEY_COUNTRY_ID = 'country_id';
    const KEY_REGION = 'region';
    const KEY_REGION_ID = 'region_id';
    const KEY_REGION_CODE = 'region_code';
    const KEY_CITY = 'city';
    const KEY_STREET = 'street';
    const KEY_POSTCODE = 'postcode';
    const KEY_TELEPHONE = 'telephone';

    /**
     * @return string|null
     */
    public function getCode(): ?string;

    /**
     * @param string|null $code
     * @return PickupLocationInterface
     */
    public function setCode(?string $code): PickupLocationInterface;

    /**
     * @return int|null
     */
    public function getShippingOptionId(): ?int;

    /**
     * @param int|null $shippingOptionId
     * @return PickupLocationInterface
     */
    public function setShippingOptionId(?int $shippingOptionId): PickupLocationInterface;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param string|null $name
     * @return PickupLocationInterface
     */
    public function setName(?string $name): PickupLocationInterface;

    /**
     * @return string|null
     */
    public function getEmail(): ?string;

    /**
     * @param string|null $email
     * @return PickupLocationInterface
     */
    public function setEmail(?string $email): PickupLocationInterface;

    /**
     * @return string|null
     */
    public function getFax(): ?string;

    /**
     * @param string|null $fax
     * @return PickupLocationInterface
     */
    public function setFax(?string $fax): PickupLocationInterface;

    /**
     * @return string|null
     */
    public function getContactName(): ?string;

    /**
     * @param string|null $contactName
     * @return PickupLocationInterface
     */
    public function setContactName(?string $contactName): PickupLocationInterface;

    /**
     * @return float|null
     */
    public function getLatitude(): ?float;

    /**
     * @param float|null $latitude
     * @return PickupLocationInterface
     */
    public function setLatitude(?float $latitude): PickupLocationInterface;

    /**
     * @return float|null
     */
    public function getLongitude(): ?float;

    /**
     * @param float|null $longitude
     * @return PickupLocationInterface
     */
    public function setLongitude(?float $longitude): PickupLocationInterface;

    /**
     * @return string|null
     */
    public function getCountryId(): ?string;

    /**
     * @param string|null $countryId
     * @return PickupLocationInterface
     */
    public function setCountryId(?string $countryId): PickupLocationInterface;

    /**
     * @return string|null
     */
    public function getRegion(): ?string;

    /**
     * @param string|null $region
     * @return PickupLocationInterface
     */
    public function setRegion(?string $region): PickupLocationInterface;

    /**
     * @return int
     */
    public function getRegionId(): ?int;

    /**
     * @param int|null $regionId
     * @return PickupLocationInterface
     */
    public function setRegionId(?int $regionId): PickupLocationInterface;

    /**
     * @return string|null
     */
    public function getRegionCode(): ?string;

    /**
     * @param string|null $regionCode
     * @return PickupLocationInterface
     */
    public function setRegionCode(?string $regionCode): PickupLocationInterface;

    /**
     * @return string|null
     */
    public function getCity(): ?string;

    /**
     * @param string|null $city
     * @return PickupLocationInterface
     */
    public function setCity(?string $city): PickupLocationInterface;

    /**
     * @return string|null
     */
    public function getStreet(): ?string;

    /**
     * @param string|null $street
     * @return PickupLocationInterface
     */
    public function setStreet(?string $street): PickupLocationInterface;

    /**
     * @return string|null
     */
    public function getPostcode(): ?string;

    /**
     * @param string|null $postcode
     * @return PickupLocationInterface
     */
    public function setPostcode(?string $postcode): PickupLocationInterface;

    /**
     * @return string|null
     */
    public function getTelephone(): ?string;

    /**
     * @param string|null $telephone
     * @return PickupLocationInterface
     */
    public function setTelephone(?string $telephone): PickupLocationInterface;
}
