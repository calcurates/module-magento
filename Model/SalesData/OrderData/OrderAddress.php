<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\SalesData\OrderData;

use Calcurates\ModuleMagento\Api\SalesData\OrderData\OrderAddressExtensionAttributesInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\OrderAddressData as Resource;
use Magento\Framework\Model\AbstractModel;

class OrderAddress extends AbstractModel implements OrderAddressExtensionAttributesInterface
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
        return (int)$this->getData(self::MAGENTO_ORDER_ADDRESS_ID);
    }

    /**
     * @inheritdoc
     */
    public function setAddressId(int $addressId): OrderAddressExtensionAttributesInterface
    {
        return $this->setData(self::MAGENTO_ORDER_ADDRESS_ID, $addressId);
    }

    /**
     * @inheritdoc
     */
    public function getResidentialDelivery(): ?int
    {
        return null !== $this->getData(static::EXT_ATTRIBUTE_RESIDENTIAL_DELIVERY)
            ? (int)$this->getData(static::EXT_ATTRIBUTE_RESIDENTIAL_DELIVERY)
            : null;
    }

    /**
     * @inheritdoc
     */
    public function setResidentialDelivery(?int $residentialDelivery): OrderAddressExtensionAttributesInterface
    {
        return $this->setData(self::EXT_ATTRIBUTE_RESIDENTIAL_DELIVERY, $residentialDelivery);
    }
}
