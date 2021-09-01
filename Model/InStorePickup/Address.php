<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\InStorePickup;

use Calcurates\ModuleMagento\Api\Data\InStorePickup\AddressInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Class Address
 */
class Address extends AbstractSimpleObject implements AddressInterface
{
    /**
     * @inheritDoc
     */
    public function getRegion()
    {
        return $this->_get(AddressInterface::KEY_REGION);
    }

    /**
     * @inheritDoc
     */
    public function setRegion($region)
    {
        return $this->setData(AddressInterface::KEY_REGION, $region);
    }

    /**
     * @inheritDoc
     */
    public function getRegionId()
    {
        return $this->_get(AddressInterface::KEY_REGION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setRegionId($regionId)
    {
        return $this->setData(AddressInterface::KEY_REGION_ID, $regionId);
    }

    /**
     * @inheritDoc
     */
    public function getRegionCode()
    {
        return $this->_get(AddressInterface::KEY_REGION_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setRegionCode($regionCode)
    {
        return $this->setData(AddressInterface::KEY_REGION_CODE, $regionCode);
    }

    /**
     * @inheritDoc
     */
    public function getCountryId()
    {
        return $this->_get(AddressInterface::KEY_COUNTRY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCountryId($countryId)
    {
        return $this->setData(AddressInterface::KEY_COUNTRY_ID, $countryId);
    }

    /**
     * @inheritDoc
     */
    public function getStreet()
    {
        return $this->_get(AddressInterface::KEY_STREET);
    }

    /**
     * @inheritDoc
     */
    public function setStreet($street)
    {
        return $this->setData(AddressInterface::KEY_STREET, $street);
    }

    /**
     * @inheritDoc
     */
    public function getCompany()
    {
        return $this->_get(AddressInterface::KEY_COMPANY);
    }

    /**
     * @inheritDoc
     */
    public function setCompany($company)
    {
        return $this->setData(AddressInterface::KEY_COMPANY, $company);
    }

    /**
     * @inheritDoc
     */
    public function getTelephone()
    {
        return $this->_get(AddressInterface::KEY_TELEPHONE);
    }

    /**
     * @inheritDoc
     */
    public function setTelephone($telephone)
    {
        return $this->setData(AddressInterface::KEY_TELEPHONE, $telephone);
    }

    /**
     * @inheritDoc
     */
    public function getFax()
    {
        return $this->_get(AddressInterface::KEY_FAX);
    }

    /**
     * @inheritDoc
     */
    public function setFax($fax)
    {
        return $this->setData(AddressInterface::KEY_FAX, $fax);
    }

    /**
     * @inheritDoc
     */
    public function getPostcode()
    {
        return $this->_get(AddressInterface::KEY_POSTCODE);
    }

    /**
     * @inheritDoc
     */
    public function setPostcode($postcode)
    {
        return $this->setData(AddressInterface::KEY_POSTCODE, $postcode);
    }

    /**
     * @inheritDoc
     */
    public function getCity()
    {
        return $this->_get(AddressInterface::KEY_CITY);
    }

    /**
     * @inheritDoc
     */
    public function setCity($city)
    {
        return $this->setData(AddressInterface::KEY_CITY, $city);
    }
}
