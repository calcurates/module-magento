<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Config\Source;

class RateTaxDisplaySource implements \Magento\Framework\Data\OptionSourceInterface
{
    public const TAX_EXCLUDED = 0;
    public const TAX_INCLUDED = 1;
    public const BOTH = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('with tax and duties included (landed cost)'), 'value' => self::TAX_INCLUDED],
            ['label' => __('without tax and duties'), 'value' => self::TAX_EXCLUDED],
            ['label' => __('both options - with and without tax & duties'), 'value' => self::BOTH],
        ];
    }
}
