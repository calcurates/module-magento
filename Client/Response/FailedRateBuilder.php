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
use Magento\Quote\Model\Quote\Address\RateResult\Error;
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
     * @param string $methodTitle
     * @param string $message
     * @param int|null $priority
     * @return Error
     */
    public function build(string $rateName, string $methodTitle = '', string $message = '', ?int $priority = null)
    {
        $failedRate = $this->rateErrorFactory->create();
        $failedRate->setCarrier(Carrier::CODE);
        $failedRate->setCarrierTitle($rateName);
        $failedRate->setMethodTitle($methodTitle);
        $failedRate->setErrorMessage($message);
        $failedRate->setPriority($priority);

        return $failedRate;
    }
}
