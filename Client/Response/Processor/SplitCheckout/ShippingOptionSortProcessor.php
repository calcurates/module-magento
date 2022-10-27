<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor\SplitCheckout;

use Calcurates\ModuleMagento\Client\Response\ResponseProcessorInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Shipping\Model\Rate\Result;

class ShippingOptionSortProcessor implements ResponseProcessorInterface
{
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
        $response['shippingOptions'] = $resultShippingOptions;
    }

    /**
     * @param array $resultShippingOptions
     * @param string $type
     */
    private function prioritySortByType(array &$resultShippingOptions, $type): void
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
     * @param array $resultShippingOptions
     */
    private function sortTableRates(array &$resultShippingOptions): void
    {
        if (!$resultShippingOptions['tableRates']) {
            return;
        }

        foreach ($resultShippingOptions['tableRates'] as &$tableRate) {
            \usort($tableRate['methods'], static function (array $firstMethod, array $secondMethod): int {
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

        \usort($resultShippingOptions['tableRates'], static function (array $firstRate, array $secondRate): int {
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
     * @param array $resultShippingOptions
     */
    private function sortInStorePickups(array &$resultShippingOptions): void
    {
        if (!$resultShippingOptions['inStorePickups']) {
            return;
        }

        foreach ($resultShippingOptions['inStorePickups'] as &$inStorePickup) {
            \usort($inStorePickup['stores'], static function (array $firstStore, array $secondStore): int {
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

        \usort($resultShippingOptions['inStorePickups'], static function (array $firstRate, array $secondRate): int {
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
     * @param array $resultShippingOptions
     */
    private function sortCarriers(array &$resultShippingOptions): void
    {
        if (!$resultShippingOptions['carriers']) {
            return;
        }

        foreach ($resultShippingOptions['carriers'] as &$carrier) {
            if (!$carrier['rates']) {
                continue;
            }
            \usort($carrier['rates'], static function (array $firstRate, array $secondRate): int {
                if (!$firstRate['success'] || !$firstRate['service']['rate']) {
                    return 1;
                }
                if (!$secondRate['success'] || !$secondRate['service']['rate']) {
                    return -1;
                }
                return $firstRate['service']['rate']['cost'] <=> $secondRate['service']['rate']['cost'];
            });
        }

        \usort($resultShippingOptions['carriers'], static function (array $firstCarrier, array $secondCarrier): int {
            if ($firstCarrier['priority'] === $secondCarrier['priority']) {
                if (!$firstCarrier['success'] || !$firstCarrier['rates']) {
                    return 1;
                }
                if (!$secondCarrier['success'] || !$secondCarrier['rates']) {
                    return -1;
                }

                $carrierA = $firstCarrier['rates'][0];
                $carrierB = $secondCarrier['rates'][0];

                $cheapestCostA = $carrierA['service']['rate'] ? $carrierA['service']['rate']['cost'] : null;
                $cheapestCostB = $carrierB['service']['rate'] ? $carrierB['service']['rate']['cost'] : null;

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
     * @param array $resultShippingOptions
     */
    private function sortRateShopping(array &$resultShippingOptions): void
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
                \usort($carrier['rates'], static function (array $firstRate, array $secondRate): int {
                    if (!$firstRate['success'] || !$firstRate['service']['rate']) {
                        return 1;
                    }
                    if (!$secondRate['success'] || !$secondRate['service']['rate']) {
                        return -1;
                    }
                    return $firstRate['service']['rate']['cost'] <=> $secondRate['service']['rate']['cost'];
                });
            }
        }

        \usort($resultShippingOptions['rateShopping'], static function (array $firstRate, array $secondRate): int {
            if ($firstRate['priority'] === $secondRate['priority']) {
                if (!$firstRate['carriers']) {
                    return 1;
                }
                if (!$secondRate['carriers']) {
                    return -1;
                }

                $carrierA = $firstRate['carriers'][0];
                $carrierB = $secondRate['carriers'][0];

                $cheapestCostA = $carrierA['rates'] && $carrierA['rates'][0] && $carrierA['rates'][0]['service']['rate']
                    ? $carrierA['rates'][0]['service']['rate']['cost']
                    : null;
                $cheapestCostB = $carrierB['rates'] && $carrierB['rates'][0] && $carrierB['rates'][0]['service']['rate']
                    ? $carrierB['rates'][0]['service']['rate']['cost']
                    : null;

                if (null === $cheapestCostA) {
                    return 1;
                }
                if (null === $cheapestCostB) {
                    return -1;
                }

                $result = $cheapestCostA <=> $cheapestCostB;
                if (0 === $result) {
                    $result = $carrierA['name'] <=> $carrierB['name'];
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
}
