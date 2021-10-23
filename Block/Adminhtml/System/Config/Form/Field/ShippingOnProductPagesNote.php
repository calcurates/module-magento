<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ShippingOnProductPagesNote extends Field
{
    const LINK_URL = 'https://my.calcurates.com/cabinet/marketplace';

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $urlText = __('"Shipping on Product Pages" feature');
        $urlTag = '<p style="display:inline"><a href="' . self::LINK_URL . '" target="_blank">' . $urlText . '</a></span>';

        return __(
            'Ignore this setting if you don\'t have %1 activated for your Calcurates account.',
            $urlTag
        );
    }
}
