<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\InfoMessage;

use Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\OutputProcessorInterface;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Framework\DataObject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class CartWeight implements OutputProcessorInterface
{
    /**
     * @var string
     */
    private $variableTemplate = '{cart_weight}';

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * CartWeight constructor.
     * @param ScopeConfigInterface $config
     */
    public function __construct(ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param Rate|Method|Error|DataObject $rateModel
     * @param string $stringToProcess
     * @return string
     */
    public function process(DataObject $rateModel, string $stringToProcess): string
    {
        if (false === \strpos($stringToProcess, $this->variableTemplate)) {
            return $stringToProcess;
        }
        $value = $rateModel->getAddress()->getWeight();
        $unit =  $this->config->getValue(
            'general/locale/weight_unit',
            ScopeInterface::SCOPE_STORE
        );
        return str_replace(
            $this->variableTemplate,
            sprintf('%s %s', $value, $unit),
            $stringToProcess
        );
    }
}
