<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Config\Source;

class RateTaxDisplaySource implements \Magento\Framework\Data\OptionSourceInterface
{
    const TAX_EXCLUDED = 0;
    const TAX_INCLUDED = 1;
    const BOTH = 2;

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
