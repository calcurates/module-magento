<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\InfoMessage;

use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\OutputProcessorInterface;
use Magento\Quote\Model\Quote\Address\Rate;

class TransitDays implements OutputProcessorInterface
{
    /**
     * @var array
     */
    protected $variablesTemplate = [
        'daysInTransitFrom' => '{min_transit_days}',
        'daysInTransitTo' => '{max_transit_days}'
    ];

    /**
     * @param Rate $rateModel
     * @param string $stringToProcess
     * @return string
     */
    public function process(Rate $rateModel, string $stringToProcess): string
    {
        $deliveryDates = $rateModel->getData(RatesResponseProcessor::CALCURATES_DELIVERY_DATES);
        if (!$deliveryDates) {
            return $stringToProcess;
        }

        foreach ($this->variablesTemplate as $variableKey => $variableTemplate) {
            if (false === strpos($stringToProcess, $variableTemplate)) {
                continue;
            }

            $value = $deliveryDates[$variableKey] ?? '';
            $stringToProcess = str_replace(
                $variableTemplate,
                $value,
                $stringToProcess
            );
        }

        return $stringToProcess;
    }
}
