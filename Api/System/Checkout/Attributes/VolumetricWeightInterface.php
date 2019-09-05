<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\System\Checkout\Attributes;

use Calcurates\ModuleMagento\Api\Data\Checkout\AttributeLinking\VolumetricWeightInterface as DataVolumetricWeightInterface;

/**
 * @api
 */
interface VolumetricWeightInterface
{
    /**
     * @param \Calcurates\ModuleMagento\Api\Data\Checkout\AttributeLinking\VolumetricWeightInterface $attributes
     *
     * @param int $websiteId
     *
     * @return void
     */
    public function link(DataVolumetricWeightInterface $attributes, $websiteId);
}
