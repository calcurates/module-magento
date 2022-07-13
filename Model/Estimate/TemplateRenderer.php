<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Estimate;

class TemplateRenderer
{
    /**
     * @param string $template
     * @param array $variables
     * @return string
     */
    public function render(string $template, array $variables): string
    {
        $vars = array_map(
            static function ($field) {
                return "{{$field}}";
            },
            array_keys($variables)
        );
        $values = array_values($variables);

        return str_replace($vars, $values, nl2br($template));
    }
}
