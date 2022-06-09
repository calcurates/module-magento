<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor\SplitCheckout\Utils;

/**
 * Build long rate name with method and packages names
 */
class TableRateNameBuilder
{
    /**
     * @param array $rateMethod
     * @param bool $includePackageNames
     * @return string
     */
    public function buildName(array $rateMethod, bool $includePackageNames): string
    {
        $uniqueMethodNames = [];

        $name = $uniqueMethodNames[$rateMethod['name']] ?? $rateMethod['name'] . ' - ';

        if ($includePackageNames) {
            $packageNames = [];
            foreach ($rateMethod['rate']['packages'] ?? [] as $package) {
                $packageNames[] = $package['name'];
            }
            $name .= implode(';', $packageNames);
        }

        $uniqueMethodNames[$rateMethod['name']] = $name;

        return implode(', ', array_map(static function ($serviceName): string {
            return rtrim($serviceName, ' - ');
        }, $uniqueMethodNames));
    }
}
