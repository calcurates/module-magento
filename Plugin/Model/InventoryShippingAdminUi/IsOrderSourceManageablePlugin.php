<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\Model\InventoryShippingAdminUi;

use Magento\Framework\App\Request\DataPersistorInterface;

class IsOrderSourceManageablePlugin
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * IsOrderSourceManageablePlugin constructor.
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @param $subject
     * @param bool $result
     * @return bool
     */
    public function afterExecute($subject, bool $result): bool
    {
        if ($result && $this->dataPersistor->get('automated_source_selection')) {
            $this->dataPersistor->clear('automated_source_selection');
            return false;
        }
        return $result;
    }
}
