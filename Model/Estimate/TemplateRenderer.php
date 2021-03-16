<?php

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

        return str_replace($vars, $values, $template);
    }
}
