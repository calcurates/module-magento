<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\UI\Component\Listing\Order\Shipment\Column;

use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Carrier - Shipping Grid UI Column
 */
class Carrier extends Column
{
    /**
     * Prepare component configuration
     *
     * @return void
     * @throws LocalizedException
     */
    public function prepare()
    {
        parent::prepare();

        $config = $this->getData('config');
        array_unshift(
            $config['options'],
            [
                'label' => __('No Carrier Info'),
                'value' => null
            ]
        );
        $this->setData('config', $config);
    }
}
