<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Estimate;

use Magento\Framework\Escaper;

class TemplateRenderer
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param Escaper $escaper
     */
    public function __construct(Escaper $escaper)
    {
        $this->escaper = $escaper;
    }

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

        return str_replace(
            $vars,
            $values,
            $this->escaper->escapeHtml(
                nl2br($template),
                ['span', 'b', 'strong', 'div', 'ul', 'li', 'i', 'em', 'br', 'hr']
            )
        );
    }
}
