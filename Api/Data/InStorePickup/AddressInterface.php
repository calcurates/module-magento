<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data\InStorePickup;

/**
 * Interface AddressInterface
 */
interface AddressInterface
{
    const KEY_CODE = 'code';

    const KEY_COUNTRY_ID = 'country_id';

    const KEY_REGION_ID = 'region_id';

    const KEY_REGION_CODE = 'region_code';

    const KEY_REGION = 'region';

    const KEY_STREET = 'street';

    const KEY_COMPANY = 'company';

    const KEY_TELEPHONE = 'telephone';

    const KEY_FAX = 'fax';

    const KEY_POSTCODE = 'postcode';

    const KEY_CITY = 'city';

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getRegion();

    /**
     * @param string $region
     * @return $this
     */
    public function setRegion($region);

    /**
     * @return int
     */
    public function getRegionId();

    /**
     * @param int $regionId
     * @return $this
     */
    public function setRegionId($regionId);

    /**
     * @return string
     */
    public function getRegionCode();

    /**
     * @param string $regionCode
     * @return $this
     */
    public function setRegionCode($regionCode);

    /**
     * @return string
     */
    public function getCountryId();

    /**
     * @param string $countryId
     * @return $this
     */
    public function setCountryId($countryId);

    /**
     * @return string[]
     */
    public function getStreet();

    /**
     * @param string|string[] $street
     * @return $this
     */
    public function setStreet($street);

    /**
     * @return string|null
     */
    public function getCompany();

    /**
     * @param string $company
     * @return $this
     */
    public function setCompany($company);

    /**
     * @return string
     */
    public function getTelephone();

    /**
     * @param string $telephone
     * @return $this
     */
    public function setTelephone($telephone);

    /**
     * @return string|null
     */
    public function getFax();

    /**
     * @param string $fax
     * @return $this
     */
    public function setFax($fax);

    /**
     * @return string
     */
    public function getPostcode();

    /**
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode);

    /**
     * @return string
     */
    public function getCity();

    /**
     * @param string $city
     * @return $this
     */
    public function setCity($city);
}
