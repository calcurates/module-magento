<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor\Utils;

class ChildChecker
{
    /**
     * @param array $parent
     * @param string $childKey
     * @return bool
     */
    public function isHaveRates(array $parent, string $childKey): bool
    {
        if ($parent['success']) {
            return true;
        }

        if ($parent[$childKey]) {
            foreach ($parent[$childKey] as $child) {
                if ($child['message']) {
                    return true;
                }
            }
        }

        return false;
    }
}
