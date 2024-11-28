<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\Model\Rule\Condition;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\SalesRule\Model\Rule\Condition\Address;

class AddressShippingOptionsPlugin
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * AddressShippingOptionsPlugin constructor.
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(DataPersistorInterface $dataPersistor)
    {
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @param Address $subject
     */
    public function beforeGetValueSelectOptions(Address $subject)
    {
        if ($subject->getAttribute() == 'shipping_method') {
            $this->dataPersistor->set('all_methods_shipping_rule', true);
        }
    }
}
