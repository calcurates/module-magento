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
use Magento\Quote\Api\Data\CartInterface;
use Magento\Shipping\Model\Rate\Result;

class ShippingOptionSortProcessor implements ResponseProcessorInterface
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
     * FlatRateProcessor constructor.
     * @param FailedRateBuilder $failedRateBuilder
     * @param RateBuilder $rateBuilder
     */
    public function __construct(FailedRateBuilder $failedRateBuilder, RateBuilder $rateBuilder)
    {
        $this->failedRateBuilder = $failedRateBuilder;
        $this->rateBuilder = $rateBuilder;
    }

    /**
     * @param Result $result
     * @param array $response
     * @param CartInterface $quote
     */
    public function process(Result $result, array &$response, CartInterface $quote): void
    {
        $resultShippingOptions = $response['shippingOptions'];
        $this->prioritySortByType($resultShippingOptions, 'flatRates');
        $this->prioritySortByType($resultShippingOptions, 'freeShipping');
        $this->sortTableRates($resultShippingOptions);
        $this->sortInStorePickups($resultShippingOptions);
        $this->sortCarriers($resultShippingOptions);
        $this->sortRateShopping($resultShippingOptions);
        $this->sortMergedShippingOptions($resultShippingOptions);
        $response['shippingOptions'] = $resultShippingOptions;
    }


    /**
     * @param $resultShippingOptions
     * @param $type
     */
    private function prioritySortByType(&$resultShippingOptions, $type): void
    {
        if (!$resultShippingOptions[$type]) {
            return;
        }
        \usort($resultShippingOptions[$type], static function ($firstRate, $secondRate): int {
            if ($firstRate['priority'] === $secondRate['priority']) {
                if (!$firstRate['success'] || !$firstRate['rate']) {
                    return 1;
                }
                if (!$secondRate['success'] || !$secondRate['rate']) {
                    return -1;
                }

                $result = $firstRate['rate']['cost'] <=> $secondRate['rate']['cost'];
                if (0 === $result) {
                    $result = $firstRate['name'] <=> $secondRate['name'];
                }

                return $result;
            }
            if (null === $firstRate['priority']) {
                return 1;
            }
            if (null === $secondRate['priority']) {
                return -1;
            }

            return $firstRate['priority'] <=> $secondRate['priority'];
        });
    }

    /**
     * @param $resultShippingOptions
     */
    private function sortTableRates(&$resultShippingOptions): void
    {
        if (!$resultShippingOptions['tableRates']) {
            return;
        }

        foreach ($resultShippingOptions['tableRates'] as &$tableRate) {
            \usort($tableRate['methods'], static function ($firstMethod, $secondMethod): int {
                if (!$firstMethod['success'] || !$firstMethod['rate']) {
                    return 1;
                }
                if (!$secondMethod['success'] || !$secondMethod['rate']) {
                    return -1;
                }

                $result = $firstMethod['rate']['cost'] <=> $secondMethod['rate']['cost'];
                if (0 === $result) {
                    $result = $firstMethod['name'] <=> $secondMethod['name'];
                }

                return $result;
            });
        }

        \usort($resultShippingOptions['tableRates'], static function ($firstRate, $secondRate): int {
            if ($firstRate['priority'] === $secondRate['priority']) {
                if (!$firstRate['success'] || !$firstRate['methods']) {
                    return 1;
                }
                if (!$secondRate['success'] || !$secondRate['methods']) {
                    return -1;
                }
                $methodA = $firstRate['methods'][0];
                $methodB = $secondRate['methods'][0];

                $cheapestCostA = $methodA['rate'] ? $methodA['rate']['cost'] : null;
                $cheapestCostB = $methodB['rate'] ? $methodB['rate']['cost'] : null;

                if (null === $cheapestCostA) {
                    return 1;
                }
                if (null === $cheapestCostB) {
                    return -1;
                }

                $result = $cheapestCostA <=> $cheapestCostB;
                if (0 === $result) {
                    $result = $methodA['name'] <=> $methodB['name'];
                }

                return $result;
            }
            if (null === $firstRate['priority']) {
                return 1;
            }
            if (null === $secondRate['priority']) {
                return -1;
            }

            return $firstRate['priority'] <=> $secondRate['priority'];
        });
    }

    /**
     * @param $resultShippingOptions
     */
    private function sortInStorePickups(&$resultShippingOptions): void
    {
        if (!$resultShippingOptions['inStorePickups']) {
            return;
        }

        foreach ($resultShippingOptions['inStorePickups'] as &$inStorePickup) {
            \usort($inStorePickup['stores'], static function ($firstStore, $secondStore): int {
                if (!$firstStore['success'] || !$firstStore['rate']) {
                    return 1;
                }
                if (!$secondStore['success'] || !$secondStore['rate']) {
                    return -1;
                }

                $result = $firstStore['rate']['cost'] <=> $secondStore['rate']['cost'];
                if (0 === $result) {
                    $result = $firstStore['name'] <=> $secondStore['name'];
                }

                return $result;
            });
        }

        \usort($resultShippingOptions['inStorePickups'], static function ($firstRate, $secondRate): int {
            if ($firstRate['priority'] === $secondRate['priority']) {
                if (!$firstRate['success'] || !$firstRate['stores']) {
                    return 1;
                }
                if (!$secondRate['success'] || !$secondRate['stores']) {
                    return -1;
                }


                $inStorePickupStoreA = $firstRate['stores'][0];
                $inStorePickupStoreB = $secondRate['stores'][0];

                $cheapestCostA = $inStorePickupStoreA['rate'] ? $inStorePickupStoreA['rate']['cost'] : null;
                $cheapestCostB = $inStorePickupStoreB['rate'] ? $inStorePickupStoreB['rate']['cost'] : null;

                if (null === $cheapestCostA) {
                    return 1;
                }
                if (null === $cheapestCostB) {
                    return -1;
                }

                $result = $cheapestCostA <=> $cheapestCostB;
                if (0 === $result) {
                    $result = $inStorePickupStoreA['name'] <=> $inStorePickupStoreB['name'];
                }

                return $result;
            }
            if (null === $firstRate['priority']) {
                return 1;
            }
            if (null === $secondRate['priority']) {
                return -1;
            }

            return $firstRate['priority'] <=> $secondRate['priority'];
        });
    }

    /**
     * @param $resultShippingOptions
     */
    private function sortCarriers(&$resultShippingOptions): void
    {
        if (!$resultShippingOptions['carriers']) {
            return;
        }

        foreach ($resultShippingOptions['carriers'] as &$carrier) {
            if (!$carrier['rates']) {
                continue;
            }
            \usort($carrier['rates'], static function ($firstRate, $secondRate): int {
                if (!$firstRate['success'] || !$firstRate['rate']) {
                    return 1;
                }
                if (!$secondRate['success'] || !$secondRate['rate']) {
                    return -1;
                }
                return $firstRate['rate']['cost'] <=> $secondRate['rate']['cost'];
            });
        }

        \usort($resultShippingOptions['carriers'], static function ($firstCarrier, $secondCarrier): int {
            if ($firstCarrier['priority'] === $secondCarrier['priority']) {
                if (!$firstCarrier['success'] || !$firstCarrier['rates']) {
                    return 1;
                }
                if (!$secondCarrier['success'] || !$secondCarrier['rates']) {
                    return -1;
                }

                $carrierA = $firstCarrier['rates'][0];
                $carrierB = $secondCarrier['rates'][0];

                $cheapestCostA = $carrierA['rate'] ? $carrierA['rate']['cost'] : null;
                $cheapestCostB = $carrierB['rate'] ? $carrierB['rate']['cost'] : null;

                if (null === $cheapestCostA) {
                    return 1;
                }
                if (null === $cheapestCostB) {
                    return -1;
                }

                return $cheapestCostA <=> $cheapestCostB;
            }
            if (null === $firstCarrier['priority']) {
                return 1;
            }
            if (null === $secondCarrier['priority']) {
                return -1;
            }

            return $firstCarrier['priority'] <=> $secondCarrier['priority'];
        });
    }

    /**
     * @param $resultShippingOptions
     */
    private function sortRateShopping(&$resultShippingOptions): void
    {
        if (!$resultShippingOptions['rateShopping']) {
            return;
        }

        foreach ($resultShippingOptions['rateShopping'] as &$rateShopping) {
            if (!$rateShopping['carriers']) {
                continue;
            }
            foreach ($rateShopping['carriers'] as &$carrier) {
                if (!$carrier['rates']) {
                    continue;
                }
                \usort($carrier['rates'], static function ($firstRate, $secondRate): int {
                    if (!$firstRate['success'] || !$firstRate['rate']) {
                        return 1;
                    }
                    if (!$secondRate['success'] || !$secondRate['rate']) {
                        return -1;
                    }
                    return $firstRate['rate']['cost'] <=> $secondRate['rate']['cost'];
                });
            }
        }

        \usort($resultShippingOptions['rateShopping'], static function ($firstRate, $secondRate): int {
            if ($firstRate['priority'] === $secondRate['priority']) {
                if (!$firstRate['carriers']) {
                    return 1;
                }
                if (!$secondRate['carriers']) {
                    return -1;
                }

                $carrierA = $firstRate['carriers'][0];
                $carrierB = $secondRate['carriers'][0];

                $cheapestCostA = $carrierA['rates'] && $carrierA['rates'][0] && $carrierA['rates'][0]['rate']
                    ? $carrierA['rates'][0]['rate']['cost']
                    : null;
                $cheapestCostB = $carrierB['rates'] && $carrierB['rates'][0] && $carrierB['rates'][0]['rate']
                    ? $carrierB['rates'][0]['rate']['cost']
                    : null;

                if (null === $cheapestCostA) {
                    return 1;
                }
                if (null === $cheapestCostB) {
                    return -1;
                }

                $result = $cheapestCostA <=> $cheapestCostB;
                if (0 === $result) {
                    $result = $carrierB['name'] <=> $carrierB['name'];
                }

                return $result;
            }
            if (null === $firstRate['priority']) {
                return 1;
            }
            if (null === $secondRate['priority']) {
                return -1;
            }

            return $firstRate['priority'] <=> $secondRate['priority'];
        });
    }

    /**
     * @param $resultShippingOptions
     */
    private function sortMergedShippingOptions(&$resultShippingOptions): void
    {
        if (!$resultShippingOptions['mergedShippingOptions']) {
            return;
        }
        \usort(
            $resultShippingOptions['mergedShippingOptions'],
            static function ($firstMethod, $secondMethod): int {
                if (!$firstMethod['success'] || !$firstMethod['rate']) {
                    return 1;
                }
                if (!$secondMethod['success'] || !$secondMethod['rate']) {
                    return -1;
                }

                $result = $firstMethod['rate']['cost'] <=> $secondMethod['rate']['cost'];
                if (0 === $result) {
                    $result = $firstMethod['name'] <=> $secondMethod['name'];
                }

                return $result;
            }
        );
    }
}