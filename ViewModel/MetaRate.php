<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\ViewModel;

use Calcurates\ModuleMagento\Api\Data\MetaRateDataInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class MetaRate implements ArgumentInterface
{
    private $metaRateData;

    public function __construct(
        MetaRateDataInterface $metaRateData
    ) {
        $this->metaRateData = $metaRateData;
    }

    public function getMetaRateData()
    {
        return $this->metaRateData;
    }
}
