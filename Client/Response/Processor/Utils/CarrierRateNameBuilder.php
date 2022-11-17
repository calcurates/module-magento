<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor\Utils;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;

/**
 * Build long carrier rate name with services' names (unique) and packages' names in it
 */
class CarrierRateNameBuilder
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @param State $appState
     */
    public function __construct(State $appState)
    {
        $this->appState = $appState;
    }

    /**
     * @param array $carrierRate
     * @param bool $includePackageNames
     * @return string
     */
    public function buildName(array $carrierRate, bool $includePackageNames): string
    {
        $uniqueServiceNames = [];
        foreach ($carrierRate['services'] as $service) {
            if ($this->appState->getAreaCode() === Area::AREA_ADMINHTML) {
                $name = $service['name'] . (!empty($service['displayName']) ? " ({$service['displayName']})" : '');
            } else {
                $name = $service['displayName'] ?? $service['name'];
            }
            $name .= ' - ';
            $name = $uniqueServiceNames[$service['name']] ?? $name;

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
