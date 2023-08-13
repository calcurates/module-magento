<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\InfoMessage;

use Calcurates\ModuleMagento\Model\Carrier\DeliveryDateFormatter;
use Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\OutputProcessorInterface;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Address\RateResult\Method;

class DeliveryDates implements OutputProcessorInterface
{
    /**
     * @var array
     */
    protected $variables = [
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
     * @param array $data
     * @param string $stringToProcess
     * @return string
     */
    public function process(array $data, string $stringToProcess): string
    {
        foreach ($this->variables as $dateKey => $dateVariable) {
            if (substr_count($stringToProcess, $dateVariable) && array_key_exists('rate_model', $data)) {
                if (($data['rate_model'] instanceof Method || $data['rate_model'] instanceof Rate)
                    && $data['rate_model']->getData('calcurates_delivery_dates')
                ) {
                    $deliveryDates = $data['rate_model']->getData('calcurates_delivery_dates');
                    if ($deliveryDates[$dateKey]) {
                        $toDate = $this->deliveryDateFormatter->prepareDate(
                            $deliveryDates[$dateKey]
                        );
                        $resultDate = $toDate
                            ? $this->deliveryDateFormatter->formatSingleDate($toDate)
                            : '';
                    } else {
                        $resultDate = '';
                    }
                    $stringToProcess = str_replace(
                        $dateVariable,
                        $resultDate,
                        $stringToProcess
                    );
                }
            }
        }
        return $stringToProcess;
    }
}
