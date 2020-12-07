<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Source\Data;

use Calcurates\ModuleMagento\Api\Source\Data\SourceInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class Source extends AbstractSimpleObject implements SourceInterface
{
    /**
     * @return string|null
     */
    public function getSourceCode(): ?string
    {
        return $this->_get(self::SOURCE_CODE);
    }

    /**
     * @param string|null $sourceCode
     */
    public function setSourceCode(?string $sourceCode): void
    {
        $this->setData(self::SOURCE_CODE, $sourceCode);
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->_get(self::NAME);
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->setData(self::NAME, $name);
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->_get(self::EMAIL);
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->setData(self::EMAIL, $email);
    }

    /**
     * @return string|null
     */
    public function getContactName(): ?string
    {
        return $this->_get(self::CONTACT_NAME);
    }

    /**
     * @param string|null $contactName
     */
    public function setContactName(?string $contactName): void
    {
        $this->setData(self::CONTACT_NAME, $contactName);
    }

    /**
     * @return bool|null
     */
    public function isEnabled(): ?bool
    {
        return $this->_get(self::ENABLED) === null ?
            null :
            (bool)$this->_get(self::ENABLED);
    }

    /**
     * @param bool|null $enabled
     */
    public function setEnabled(?bool $enabled): void
    {
        $this->setData(self::ENABLED, $enabled);
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->_get(self::DESCRIPTION);
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @return float|null
     */
    public function getLatitude(): ?float
    {
        return $this->_get(self::LATITUDE) === null ?
            null :
            (float)$this->_get(self::LATITUDE);
    }

    /**
     * @param float|null $latitude
     */
    public function setLatitude(?float $latitude): void
    {
        $this->setData(self::LATITUDE, $latitude);
    }

    /**
     * @return float|null
     */
    public function getLongitude(): ?float
    {
        return $this->_get(self::LONGITUDE) === null ?
            null :
            (float)$this->_get(self::LONGITUDE);
    }

    /**
     * @param float|null $longitude
     */
    public function setLongitude(?float $longitude): void
    {
        $this->setData(self::LONGITUDE, $longitude);
    }

    /**
     * @return string|null
     */
    public function getCountryId(): ?string
    {
        return $this->_get(self::COUNTRY_ID);
    }

    /**
     * @param string|null $countryId
     */
    public function setCountryId(?string $countryId): void
    {
        $this->setData(self::COUNTRY_ID, $countryId);
    }

    /**
     * @return int|null
     */
    public function getRegionId(): ?int
    {
        return $this->_get(self::REGION_ID) === null ?
            null :
            (int)$this->_get(self::REGION_ID);
    }

    /**
     * @param int|null $regionId
     */
    public function setRegionId(?int $regionId): void
    {
        $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * @return string|null
     */
    public function getRegion(): ?string
    {
        return $this->_get(self::REGION);
    }

    /**
     * @param string|null $region
     */
    public function setRegion(?string $region): void
    {
        $this->setData(self::REGION, $region);
    }

    /**
     * @return string|null
     */
    public function getRegionCode(): ?string
    {
        return $this->_get(self::REGION_CODE);
    }

    /**
     * @param string|null $region
     */
    public function setRegionCode(?string $region): void
    {
        $this->setData(self::REGION_CODE, $region);
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->_get(self::CITY);
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void
    {
        $this->setData(self::CITY, $city);
    }

    /**
     * @return string|null
     */
    public function getStreet(): ?string
    {
        return $this->_get(self::STREET);
    }

    /**
     * @param string|null $street
     */
    public function setStreet(?string $street): void
    {
        $this->setData(self::STREET, $street);
    }

    /**
     * @return string|null
     */
    public function getPostcode(): ?string
    {
        return $this->_get(self::POSTCODE);
    }

    /**
     * @param string|null $postcode
     */
    public function setPostcode(?string $postcode): void
    {
        $this->setData(self::POSTCODE, $postcode);
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->_get(self::PHONE);
    }

    /**
     * @param string|null $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->setData(self::PHONE, $phone);
    }

    /**
     * @return string|null
     */
    public function getFax(): ?string
    {
        return $this->_get(self::FAX);
    }

    /**
     * @param string|null $fax
     */
    public function setFax(?string $fax): void
    {
        $this->setData(self::FAX, $fax);
    }

    /**
     * @return bool|null
     */
    public function isUseDefaultCarrierConfig(): ?bool
    {
        return true;
    }

    /**
     * @param bool|null $useDefaultCarrierConfig
     */
    public function setUseDefaultCarrierConfig(?bool $useDefaultCarrierConfig): void
    {
        $this->setData(self::USE_DEFAULT_CARRIER_CONFIG, $useDefaultCarrierConfig);
    }
}
