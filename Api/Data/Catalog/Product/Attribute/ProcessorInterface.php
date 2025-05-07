<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\Data\Catalog\Product\Attribute;

use Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributesCustomDataInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

interface ProcessorInterface
{
    /**
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @param int $websiteId
     * @return \Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributesCustomDataInterface|null
     */
    public function process(ProductAttributeInterface $attribute, int $websiteId): ?AttributesCustomDataInterface;

    /**
     * @param string $attributeCode
     * @return bool
     */
    public function canProcess(string $attributeCode): bool;
}
