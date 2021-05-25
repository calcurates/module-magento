<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Config\Source;

class DeliveryDateDisplaySource implements \Magento\Framework\Data\OptionSourceInterface
{
    public const DO_NOT_SHOW = 'do_not_show';
    public const TOOLTIP = 'tooltip';
    public const AFTER_METHOD_NAME = 'after_method_name';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Do not show'), 'value' => self::DO_NOT_SHOW],
            ['label' => __('Show in the tooltip'), 'value' => self::TOOLTIP],
            ['label' => __('Show next to the method name'), 'value' => self::AFTER_METHOD_NAME],
        ];
    }
}
