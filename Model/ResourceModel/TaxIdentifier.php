<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class TaxIdentifier extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'calcurates_tax_identifiers_resource_model';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('calcurates_tax_identifiers', 'id');
        $this->_useIsObjectNew = true;
    }
}
