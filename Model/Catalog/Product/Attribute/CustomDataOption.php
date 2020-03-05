<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Catalog\Product\Attribute;

use Calcurates\ModuleMagento\Api\Data\Catalog\Product\AttributeCustomDataOptionInterface;

class CustomDataOption extends \Magento\Framework\DataObject implements AttributeCustomDataOptionInterface
{
    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return (string)$this->_getData(self::VALUE);
    }

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return (string)$this->_getData(self::LABEL);
    }

    /**
     * @inheritDoc
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * @inheritDoc
     */
    public function setLabel($label)
    {
        return $this->setData(self::LABEL, $label);
    }
}
