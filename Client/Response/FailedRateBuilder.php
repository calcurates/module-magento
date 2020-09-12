<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response;

use Calcurates\ModuleMagento\Model\Carrier;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;

class FailedRateBuilder
{
    /**
     * @var ErrorFactory
     */
    private $rateErrorFactory;

    /**
     * FailedRateBuilder constructor.
     * @param ErrorFactory $rateErrorFactory
     */
    public function __construct(ErrorFactory $rateErrorFactory)
    {
        $this->rateErrorFactory = $rateErrorFactory;
    }

    /**
     * @param string $rateName
     * @param string $message
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Error
     */
    public function build(string $rateName, string $message = '')
    {
        $failedRate = $this->rateErrorFactory->create();
        $failedRate->setCarrier(Carrier::CODE);
        $failedRate->setCarrierTitle($rateName);
        $failedRate->setErrorMessage($message);

        return $failedRate;
    }
}
