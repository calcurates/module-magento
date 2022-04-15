<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\SalesData\QuoteData;

use Magento\Framework\Model\AbstractModel;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\QuoteAddressExtensionAttributesInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\QuoteAddressData as Resource;

class QuoteAddress extends AbstractModel implements QuoteAddressExtensionAttributesInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Resource::class);
    }

    /**
     * @inheritdoc
     */
    public function getAddressId(): int
    {
        return (int)$this->getData(self::MAGENTO_QUOTE_ADDRESS_ID);
    }

    /**
     * @inheritdoc
     */
    public function setAddressId(int $addressId): QuoteAddressExtensionAttributesInterface
    {
        return $this->setData(self::MAGENTO_QUOTE_ADDRESS_ID, $addressId);
    }

    /**
     * @inheritdoc
     */
    public function getResidentialDelivery(): ?int
    {
        return $this->getData(static::EXT_ATTRIBUTE_RESIDENTIAL_DELIVERY);
    }

    /**
     * @inheritdoc
     */
    public function setResidentialDelivery(?int $residentialDelivery): QuoteAddressExtensionAttributesInterface
    {
        return $this->setData(self::EXT_ATTRIBUTE_RESIDENTIAL_DELIVERY, $residentialDelivery);
    }
}
