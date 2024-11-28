<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\InStorePickup;

use Calcurates\ModuleMagento\Api\Data\InStorePickup\PickupLocationInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class PickupLocation extends AbstractSimpleObject implements PickupLocationInterface
{
    /**
     * @inheritdoc
     */
    public function getCode(): ?string
    {
        return $this->_get(PickupLocationInterface::KEY_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setCode(?string $code): PickupLocationInterface
    {
        return $this->setData(PickupLocationInterface::KEY_CODE, $code);
    }

    /**
     * @inheritdoc
     */
    public function getShippingOptionId(): ?int
    {
        return $this->_get(PickupLocationInterface::KEY_SHIPPING_OPTION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setShippingOptionId(?int $shippingOptionId): PickupLocationInterface
    {
        return $this->setData(PickupLocationInterface::KEY_SHIPPING_OPTION_ID, $shippingOptionId);
    }

    /**
     * @inheritdoc
     */
    public function getName(): ?string
    {
        return $this->_get(PickupLocationInterface::KEY_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName(?string $name): PickupLocationInterface
    {
        return $this->setData(PickupLocationInterface::KEY_NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getEmail(): ?string
    {
        return $this->_get(PickupLocationInterface::KEY_EMAIL);
    }

    /**
     * @inheritdoc
     */
    public function setEmail(?string $email): PickupLocationInterface
    {
        return $this->setData(PickupLocationInterface::KEY_EMAIL, $email);
    }

    /**
     * @inheritdoc
     */
    public function getFax(): ?string
    {
        return $this->_get(PickupLocationInterface::KEY_FAX);
    }

    /**
     * @inheritdoc
     */
    public function setFax(?string $fax): PickupLocationInterface
    {
        return $this->setData(PickupLocationInterface::KEY_FAX, $fax);
    }

    /**
     * @inheritdoc
     */
    public function getContactName(): ?string
    {
        return $this->_get(PickupLocationInterface::KEY_CONTACT_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setContactName(?string $contactName): PickupLocationInterface
    {
        return $this->setData(PickupLocationInterface::KEY_CONTACT_NAME, $contactName);
    }

    /**
     * @inheritdoc
     */
    public function getLatitude(): ?float
    {
        return $this->_get(PickupLocationInterface::KEY_LATITUDE);
    }

    /**
     * @inheritdoc
     */
    public function setLatitude(?float $latitude): PickupLocationInterface
    {
        return $this->setData(PickupLocationInterface::KEY_LATITUDE, $latitude);
    }

    /**
     * @inheritdoc
     */
    public function getLongitude(): ?float
    {
        return $this->_get(PickupLocationInterface::KEY_LONGITUDE);
    }

    /**
     * @inheritdoc
     */
    public function setLongitude(?float $longitude): PickupLocationInterface
    {
        return $this->setData(PickupLocationInterface::KEY_LONGITUDE, $longitude);
    }

    /**
     * @inheritdoc
     */
    public function getCountryId(): ?string
    {
        return $this->_get(PickupLocationInterface::KEY_COUNTRY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCountryId(?string $countryId): PickupLocationInterface
    {
        return $this->setData(PickupLocationInterface::KEY_COUNTRY_ID, $countryId);
    }

    /**
     * @inheritdoc
     */
    public function getRegion(): ?string
    {
        return $this->_get(PickupLocationInterface::KEY_REGION);
    }

    /**
     * @inheritdoc
     */
    public function setRegion(?string $region): PickupLocationInterface
    {
        return $this->setData(PickupLocationInterface::KEY_REGION, $region);
    }

    /**
     * @inheritdoc
     */
    public function getRegionId(): ?int
    {
        return $this->_get(PickupLocationInterface::KEY_REGION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRegionId(?int $regionId): PickupLocationInterface
    {
        return $this->setData(PickupLocationInterface::KEY_REGION_ID, $regionId);
    }

    /**
     * @inheritdoc
     */
    public function getRegionCode(): ?string
    {
        return $this->_get(PickupLocationInterface::KEY_REGION_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setRegionCode(?string $regionCode): PickupLocationInterface
    {
        return $this->setData(PickupLocationInterface::KEY_REGION_CODE, $regionCode);
    }

    /**
     * @inheritdoc
     */
    public function getCity(): ?string
    {
        return $this->_get(PickupLocationInterface::KEY_CITY);
    }

    /**
     * @inheritdoc
     */
    public function setCity(?string $city): PickupLocationInterface
    {
        return $this->setData(PickupLocationInterface::KEY_CITY, $city);
    }

    /**
     * @inheritdoc
     */
    public function getStreet(): ?string
    {
        return $this->_get(PickupLocationInterface::KEY_STREET);
    }

    /**
     * @inheritdoc
     */
    public function setStreet(?string $street): PickupLocationInterface
    {
        return $this->setData(PickupLocationInterface::KEY_STREET, $street);
    }

    /**
     * @inheritdoc
     */
    public function getPostcode(): ?string
    {
        return $this->_get(PickupLocationInterface::KEY_POSTCODE);
    }

    /**
     * @inheritdoc
     */
    public function setPostcode(?string $postcode): PickupLocationInterface
    {
        return $this->setData(PickupLocationInterface::KEY_POSTCODE, $postcode);
    }

    /**
     * @inheritdoc
     */
    public function getTelephone(): ?string
    {
        return $this->_get(PickupLocationInterface::KEY_TELEPHONE);
    }

    /**
     * @inheritdoc
     */
    public function setTelephone(?string $telephone): PickupLocationInterface
    {
        return $this->setData(PickupLocationInterface::KEY_TELEPHONE, $telephone);
    }
}
