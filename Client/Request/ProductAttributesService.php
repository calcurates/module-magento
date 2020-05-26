<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client\Request;

use Magento\Catalog\Api\Data\ProductInterface;

class ProductAttributesService
{
    /**
     * @param ProductInterface $product
     * @return array
     */
    public function getAttributes(ProductInterface $product)
    {
        $attributes = [];
        foreach ($product->getData() as $key => $value) {
            if (is_object($value)) {
                continue;
            }

            $attributes[$key] = $value;
        }
        foreach ($product->getCustomAttributes() as $customAttribute) {
            $attributes[$customAttribute->getAttributeCode()] = $customAttribute->getValue();
        }

        return $attributes;
    }
}
