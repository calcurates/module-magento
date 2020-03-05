<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\Data\Catalog\Product;

interface AttributeCustomDataOptionInterface
{
    const VALUE = 'value';
    const LABEL = 'label';

    /**
     * @return string
     */
    public function getValue();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param string $value
     *
     * @return AttributeCustomDataOptionInterface
     */
    public function setValue($value);

    /**
     * @param string $label
     *
     * @return AttributeCustomDataOptionInterface
     */
    public function setLabel($label);
}
