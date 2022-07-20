<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client\Request;

use Calcurates\ModuleMagento\Api\Catalog\Product\ProductAttributesListInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class ProductAttributesService
{
    /**
     * @param ProductInterface $product
     * @return array
     */
    public function getAttributes(ProductInterface $product): array
    {
        $attributeValues = [];

        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute  $attribute */
        foreach ($product->getAttributes() as $attributeCode => $attribute) {
            if (in_array($attribute->getFrontendInput(), ProductAttributesListInterface::BANNED_INPUT_TYPES, true)) {
                continue;
            }

            $value = $product->getData($attributeCode);

            if (null === $value) {
                $customAttribute = $product->getCustomAttribute($attributeCode);
                if ($customAttribute) {
                    $value = $customAttribute->getValue();
                }
            }

            if (null === $value) {
                continue;
            }

            if ($attribute->getIsHtmlAllowedOnFront() && is_string($value)) {
                $value = strip_tags($value);
            }

            $attributeValues[$attributeCode] = $value;
        }

        return $attributeValues;
    }
}
