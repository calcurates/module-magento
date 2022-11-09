<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor;

use Calcurates\ModuleMagento\Client\RateBuilder;
use Calcurates\ModuleMagento\Client\Response\FailedRateBuilder;
use Calcurates\ModuleMagento\Client\Response\ResponseProcessorInterface;
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Shipping\Model\Rate\Result;

class FreeShippingProcessor implements ResponseProcessorInterface
{
    /**
     * @var FailedRateBuilder
     */
    private $failedRateBuilder;

    /**
     * @var RateBuilder
     */
    private $rateBuilder;

    /**
     * @var State
     */
    private $appState;

    /**
     * FreeShippingProcessor constructor.
     * @param FailedRateBuilder $failedRateBuilder
     * @param RateBuilder $rateBuilder
     * @param State $appState
     */
    public function __construct(
        FailedRateBuilder $failedRateBuilder,
        RateBuilder $rateBuilder,
        State $appState
    ) {
        $this->failedRateBuilder = $failedRateBuilder;
        $this->rateBuilder = $rateBuilder;
        $this->appState = $appState;
    }

    /**
     * @param Result $result
     * @param array $response
     * @param CartInterface $quote
     */
    public function process(Result $result, array &$response, CartInterface $quote): void
    {
        foreach ($response['shippingOptions']['freeShipping'] as $responseRate) {
            if (!$responseRate['success']) {
                if ($responseRate['message']) {
                    $failedRate = $this->failedRateBuilder->build(
                        $responseRate['name'],
                        $responseRate['message'],
                        $responseRate['priority']
                    );
                    $result->append($failedRate);
                }
                continue;
            }

            $responseRate['rate']['cost'] = 0;
            $responseRate['rate']['currency'] = null;

            if ($this->appState->getAreaCode() === Area::AREA_ADMINHTML) {
                $responseRate['displayName'] = $responseRate['name']
                    . (!empty($responseRate['displayName']) ? " ({$responseRate['displayName']})" : '');
            } else {
                $responseRate['displayName'] = $responseRate['displayName'] ?? $responseRate['name'];
            }

            $rates = $this->rateBuilder->build(
                ShippingMethodManager::FREE_SHIPPING . '_' . $responseRate['id'],
                $responseRate,
                ''
            );

            foreach ($rates as $rate) {
                $result->append($rate);
            }
        }
    }
}
