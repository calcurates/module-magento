<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Model\Quote;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

class TotalsCollectorPlugin
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * TotalsCollectorPlugin constructor.
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(DataPersistorInterface $dataPersistor)
    {
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @param TotalsCollector $subject
     * @param Quote $quote
     * @param Address $address
     * @return array
     */
    public function beforeCollectAddressTotals(
        TotalsCollector $subject,
        Quote $quote,
        Address $address
    ) {
        if ($address->getVatId()) {
            $this->dataPersistor->set('vat_id', $address->getVatId());
        } else {
            $this->dataPersistor->clear('vat_id');
        }
        return [$quote, $address];
    }
}
