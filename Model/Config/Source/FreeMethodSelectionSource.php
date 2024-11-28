<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Config\Source;

use Calcurates\ModuleMagento\Client\Command\GetAllShippingOptionsCommand;
use Magento\Framework\App\Request\DataPersistorInterface;

class FreeMethodSelectionSource implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var GetAllShippingOptionsCommand
     */
    private $getAllShippingOptionsCommand;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * FreeMethodSelectionSource constructor.
     * @param GetAllShippingOptionsCommand $getAllShippingOptionsCommand
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        GetAllShippingOptionsCommand $getAllShippingOptionsCommand,
        DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->getAllShippingOptionsCommand = $getAllShippingOptionsCommand;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $setDefaultPersistor = false;
        if (!$this->dataPersistor->get('all_methods_shipping_rule')) {
            $this->dataPersistor->set('all_methods_shipping_rule', true);
            $setDefaultPersistor = true;
        }
        $methods = $this->getAllShippingOptionsCommand->getShippingOptions(0);
        if ($setDefaultPersistor) {
            $this->dataPersistor->set('all_methods_shipping_rule', false);
        }
        $result = [['value' => '', 'label' => __('None')]];
        foreach ($methods as $methodValue => $methodLabel) {
            $result[] = [
                'value' => $methodValue,
                'label' => $methodLabel
            ];
        }
        return $result;
    }
}
