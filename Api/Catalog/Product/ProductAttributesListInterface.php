<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\Catalog\Product;

/**
 * @api
 */
interface ProductAttributesListInterface
{
    const BANNED_INPUT_TYPES = ['gallery', 'media_image'];

    /**
     * @return \Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributeCustomDataInterface[]
     */
    public function getItems();
}
