<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ShippingOnProductPages implements OptionSourceInterface
{
    public const DO_NOT_DISPLAY = 0;
    public const DISPLAY_ALWAYS = 1;
    public const SPECIFIC_PRODUCT_ATTRIBUTE = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Always'), 'value' => self::DISPLAY_ALWAYS],
            ['label' => __('Only for Specific Products'), 'value' => self::SPECIFIC_PRODUCT_ATTRIBUTE],
            ['label' => __('No'), 'value' => self::DO_NOT_DISPLAY],
        ];
    }
}
