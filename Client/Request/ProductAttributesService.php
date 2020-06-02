<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client\Request;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Stdlib\StringUtils;

class ProductAttributesService
{
    /**
     * @var StringUtils
     */
    private $stringUtils;

    public function __construct(StringUtils $stringUtils)
    {
        $this->stringUtils = $stringUtils;
    }

    /**
     * @param ProductInterface $product
     * @return array
     */
    public function getAttributes(ProductInterface $product)
    {
        $attributes = [];
        foreach ($product->getData() as $key => $value) {
            if (is_object($value) || $key === 'options') {
                continue;
            }

            $attributes[$key] = $value;
        }
        foreach ($product->getCustomAttributes() as $customAttribute) {
            $attributes[$customAttribute->getAttributeCode()] = $customAttribute->getValue();
        }

        if (isset($attributes[ProductAttributeInterface::CODE_DESCRIPTION])) {
            $attributes[ProductAttributeInterface::CODE_DESCRIPTION] = $this->stripHtml(
                $attributes[ProductAttributeInterface::CODE_DESCRIPTION],
                100
            );
        }
        if (isset($attributes[ProductAttributeInterface::CODE_SHORT_DESCRIPTION])) {
            $attributes[ProductAttributeInterface::CODE_SHORT_DESCRIPTION] = $this->stripHtml(
                $attributes[ProductAttributeInterface::CODE_SHORT_DESCRIPTION],
                100
            );
        }

        return $attributes;
    }

    /**
     * @param string $value
     * @param int|null $length [optional]
     * @return string
     */
    protected function stripHtml($value, $length = null)
    {
        return $this->stringUtils->substr(strip_tags($value), 0, $length);
    }
}
