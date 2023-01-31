<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor\Utils;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;

/**
 * Build long rate name with method and packages names
 */
class TableRateNameBuilder
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
     * @param array $rateMethod
     * @param bool $includePackageNames
     * @return string
     */
    public function buildName(array $rateMethod, bool $includePackageNames): string
    {
        $uniqueMethodNames = [];
        if ($this->appState->getAreaCode() === Area::AREA_ADMINHTML) {
            $name = $rateMethod['name']
                . (!empty($rateMethod['displayName']) ? " ({$rateMethod['displayName']})" : '');
        } else {
            $name = $rateMethod['displayName'] ?? $rateMethod['name'];
        }
        $name .= ' - ';

        foreach ($rateMethod['rates'] ?? [] as $rate) {
            if ($includePackageNames) {
                $packageNames = [];
                foreach ($rate['packages'] ?? [] as $package) {
                    $packageNames[] = $package['name'];
                }
                $name .= implode(';', $packageNames);
            }
        }

        if (!empty($rateMethod['additionalText'])) {
            $name .= ' (' . implode(' ', $rateMethod['additionalText']) . ')';
        }

        $uniqueMethodNames[$rateMethod['name']] = $name;

        return implode(', ', array_map(static function (string $serviceName): string {
            return rtrim($serviceName, ' -');
        }, $uniqueMethodNames));
    }
}
