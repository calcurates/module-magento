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
 * Make some string unique with adding _{increment}.
 * Usage: $stringUniqueIncrement->getUniqueString('someString', ['someString'=>true]) will return someString_1
 */
class StringUniqueIncrement
{
    /**
     * @param string $baseString
     * @param array $existingStrings
     * @return string
     */
    public function getUniqueString(string $baseString, array $existingStrings): string
    {
        $i = 1;
        $string = $baseString;
        while (isset($existingStrings[$string])) {
            $string = $baseString . '_' . $i;
            $i++;
        }

        return $string;
    }
}
