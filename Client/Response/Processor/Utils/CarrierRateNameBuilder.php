<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor\Utils;

/**
 * Build long carrier rate name with services' names (unique) and packages' names in it
 */
class CarrierRateNameBuilder
{
    /**
     * @param array $carrierRate
     * @param bool $includePackageNames
     * @return string
     */
    public function buildName(array $carrierRate, bool $includePackageNames): string
    {
        $uniqueServiceNames = [];
        foreach ($carrierRate['services'] as $service) {
            $name = $uniqueServiceNames[$service['name']]
                ?? ($service['displayName'] ?? $service['name']) . ' - ';

            if ($includePackageNames) {
                $packageNames = [];
                foreach ($service['packages'] ?? [] as $package) {
                    $packageNames[] = $package['name'];
                }
                $name .= implode(';', $packageNames);
            }

            if (!empty($service['additionalText'])) {
                $name .= ' (' . implode(' ', $service['additionalText']) . ')';
            }

            $uniqueServiceNames[$service['name']] = $name;
        }

        return implode(', ', array_map(static function ($serviceName) {
            return rtrim($serviceName, ' - ');
        }, $uniqueServiceNames));
    }
}
