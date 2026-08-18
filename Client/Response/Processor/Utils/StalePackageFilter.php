<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor\Utils;

use Magento\Quote\Api\Data\CartInterface;

class StalePackageFilter
{
    /**
     * @param array $carrierRatesToPackages
     * @param CartInterface $quote
     * @return array
     */
    public function filter(array $carrierRatesToPackages, CartInterface $quote): array
    {
        $activeQuoteItemIds = $this->getActiveQuoteItemIds($quote);
        if (!$activeQuoteItemIds) {
            return $carrierRatesToPackages;
        }

        foreach ($carrierRatesToPackages as $carrierId => $serviceIdData) {
            if (!is_array($serviceIdData)) {
                continue;
            }

            foreach ($serviceIdData as $serviceIds => $packages) {
                if (!is_array($packages)) {
                    continue;
                }

                $prunedPackages = $this->filterPackages($packages, $activeQuoteItemIds);

                if ($prunedPackages) {
                    $carrierRatesToPackages[$carrierId][$serviceIds] = $prunedPackages;
                } else {
                    unset($carrierRatesToPackages[$carrierId][$serviceIds]);
                }
            }

            if (empty($carrierRatesToPackages[$carrierId])) {
                unset($carrierRatesToPackages[$carrierId]);
            }
        }

        return $carrierRatesToPackages;
    }

    /**
     * @param array $packages
     * @param array $activeQuoteItemIds
     * @return array
     */
    private function filterPackages(array $packages, array $activeQuoteItemIds): array
    {
        $prunedPackages = [];

        foreach ($packages as $package) {
            if (!isset($package['products']) || !is_array($package['products'])) {
                $prunedPackages[] = $package;
                continue;
            }

            $products = array_values(array_filter(
                $package['products'],
                static function ($product) use ($activeQuoteItemIds) {
                    return isset($product['quoteItemId'])
                        && isset($activeQuoteItemIds[(int)$product['quoteItemId']]);
                }
            ));

            if (!$products) {
                continue;
            }

            $package['products'] = $products;
            $prunedPackages[] = $package;
        }

        return $prunedPackages;
    }

    /**
     * @param CartInterface $quote
     * @return array
     */
    private function getActiveQuoteItemIds(CartInterface $quote): array
    {
        $activeQuoteItemIds = [];

        foreach ($quote->getAllItems() as $quoteItem) {
            $activeQuoteItemIds[(int)$quoteItem->getId()] = true;
        }

        return $activeQuoteItemIds;
    }
}
