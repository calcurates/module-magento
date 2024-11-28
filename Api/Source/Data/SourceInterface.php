<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\Source\Data;

interface SourceInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const SOURCE_CODE = 'source_code';
    public const NAME = 'name';
    public const CONTACT_NAME = 'contact_name';
    public const EMAIL = 'email';
    public const ENABLED = 'enabled';
    public const DESCRIPTION = 'description';
    public const LATITUDE = 'latitude';
    public const LONGITUDE = 'longitude';
    public const COUNTRY_ID = 'country_id';
    public const REGION_ID = 'region_id';
    public const REGION = 'region';
    public const REGION_CODE = 'region_code';
    public const CITY = 'city';
    public const STREET = 'street';
    public const POSTCODE = 'postcode';
    public const PHONE = 'phone';
    public const FAX = 'fax';
    public const USE_DEFAULT_CARRIER_CONFIG = 'use_default_carrier_config';

    /**
     * Get source code
     *
     * @return string|null
     */
    public function getSourceCode(): ?string;

    /**
     * Set source code
     *
     * @param string|null $sourceCode
     * @return void
     */
    public function setSourceCode(?string $sourceCode): void;

    /**
     * Get source name
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set source name
     *
     * @param string|null $name
     * @return void
     */
    public function setName(?string $name): void;

    /**
     * Get source email
     *
     * @return string|null
     */
    public function getEmail(): ?string;

    /**
     * Set source email
     *
     * @param string|null $email
     * @return void
     */
    public function setEmail(?string $email): void;

    /**
     * Get source contact name
     *
     * @return string|null
     */
    public function getContactName(): ?string;

    /**
     * Set source contact name
     *
     * @param string|null $contactName
     * @return void
     */
    public function setContactName(?string $contactName): void;

    /**
     * Check if source is enabled. For new entity can be null
     *
     * @return bool|null
     */
    public function isEnabled(): ?bool;

    /**
     * Enable or disable source
     *
     * @param bool|null $enabled
     * @return void
     */
    public function setEnabled(?bool $enabled): void;

    /**
     * Get source description
     *
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * Set source description
     *
     * @param string|null $description
     * @return void
     */
    public function setDescription(?string $description): void;

    /**
     * Get source latitude
     *
     * @return float|null
     */
    public function getLatitude(): ?float;

    /**
     * Set source latitude
     *
     * @param float|null $latitude
     * @return void
     */
    public function setLatitude(?float $latitude): void;

    /**
     * Get source longitude
     *
     * @return float|null
     */
    public function getLongitude(): ?float;

    /**
     * Set source longitude
     *
     * @param float|null $longitude
     * @return void
     */
    public function setLongitude(?float $longitude): void;

    /**
     * Get source country id
     *
     * @return string|null
     */
    public function getCountryId(): ?string;

    /**
     * Set source country id
     *
     * @param string|null $countryId
     * @return void
     */
    public function setCountryId(?string $countryId): void;

    /**
     * Get region id if source has registered region.
     *
     * @return int|null
     */
    public function getRegionId(): ?int;

    /**
     * Set region id if source has registered region.
     *
     * @param int|null $regionId
     * @return void
     */
    public function setRegionId(?int $regionId): void;

    /**
     * Get region title if source has custom region
     *
     * @return string|null
     */
    public function getRegion(): ?string;

    /**
     * Set source region title
     *
     * @param string|null $region
     * @return void
     */
    public function setRegion(?string $region): void;

    /**
     * Get region code if source has custom region
     *
     * @return string|null
     */
    public function getRegionCode(): ?string;

    /**
     * Set source region code
     *
     * @param string|null $region
     * @return void
     */
    public function setRegionCode(?string $region): void;

    /**
     * Get source city
     *
     * @return string|null
     */
    public function getCity(): ?string;

    /**
     * Set source city
     *
     * @param string|null $city
     * @return void
     */
    public function setCity(?string $city): void;

    /**
     * Get source street name
     *
     * @return string|null
     */
    public function getStreet(): ?string;

    /**
     * Set source street name
     *
     * @param string|null $street
     * @return void
     */
    public function setStreet(?string $street): void;

    /**
     * Get source post code
     *
     * @return string|null
     */
    public function getPostcode(): ?string;

    /**
     * Set source post code
     *
     * @param string|null $postcode
     * @return void
     */
    public function setPostcode(?string $postcode): void;

    /**
     * Get source phone number
     *
     * @return string|null
     */
    public function getPhone(): ?string;

    /**
     * Set source phone number
     *
     * @param string|null $phone
     * @return void
     */
    public function setPhone(?string $phone): void;

    /**
     * Get source fax
     *
     * @return string|null
     */
    public function getFax(): ?string;

    /**
     * Set source fax
     *
     * @param string|null $fax
     * @return void
     */
    public function setFax(?string $fax): void;

    /**
     * Check is need to use default config
     *
     * @return bool|null
     */
    public function isUseDefaultCarrierConfig(): ?bool;

    /**
     * @param bool|null $useDefaultCarrierConfig
     * @return void
     */
    public function setUseDefaultCarrierConfig(?bool $useDefaultCarrierConfig): void;
}
