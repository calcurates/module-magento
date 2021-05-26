<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Config\Source;

class DisplayInStorePickupAsSource implements \Magento\Framework\Data\OptionSourceInterface
{
    public const SHIPPING_METHODS = 'shipping_methods';
    public const STORES_SELECTOR = 'stores_selector';

    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::SHIPPING_METHODS, 'label' => __('Shipping Methods')],
            ['value' => self::STORES_SELECTOR, 'label' => __('Stores Selector')],
        ];
    }
}
