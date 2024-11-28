<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\InfoMessage;

use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Calcurates\ModuleMagento\Model\Carrier\DeliveryDateFormatter;
use Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\OutputProcessorInterface;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Framework\DataObject;

class DeliveryDates implements OutputProcessorInterface
{
    /**
     * @var array
     */
    protected $variablesTemplate = [
        'from' => '{delivery_from}',
        'to' => '{delivery_to}'
    ];

    /**
     * @var DeliveryDateFormatter
     */
    private $deliveryDateFormatter;

    /**
     * DeliveryFrom constructor.
     * @param DeliveryDateFormatter $deliveryDateFormatter
     */
    public function __construct(DeliveryDateFormatter $deliveryDateFormatter)
    {
        $this->deliveryDateFormatter = $deliveryDateFormatter;
    }

    /**
     * @param Rate|Method|Error|DataObject $rateModel
     * @param string $stringToProcess
     * @return string
     */
    public function process(DataObject $rateModel, string $stringToProcess): string
    {
        $deliveryDates = $rateModel->getData(RatesResponseProcessor::CALCURATES_DELIVERY_DATES);
        if (!$deliveryDates) {
            return $stringToProcess;
        }

        foreach ($this->variablesTemplate as $variableKey => $variableTemplate) {
            if (false === strpos($stringToProcess, $variableTemplate)) {
                continue;
            }

            if (isset($deliveryDates[$variableKey])) {
                $date = $this->deliveryDateFormatter->prepareDate($deliveryDates[$variableKey]);
                $value = $date ? $this->deliveryDateFormatter->formatSingleDate($date) : '';
            } else {
                $value = '';
            }
            $stringToProcess = str_replace(
                $variableTemplate,
                $value,
                $stringToProcess
            );
        }

        return $stringToProcess;
    }
}
