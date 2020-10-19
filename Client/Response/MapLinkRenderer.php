<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response;

class MapLinkRenderer
{
    const URL_TEMPLATE = 'https://www.google.com/maps/search/'
        . '?api=1&query={latitude},{longitude}&query_place_id={googlePlaceId}';

    /**
     * @var string|null
     */
    private $urlTemplate;

    /**
     * MapLinkRenderer constructor.
     * @param string|null $urlTemplate
     */
    public function __construct(?string $urlTemplate = null)
    {
        if ($urlTemplate === null) {
            $urlTemplate = self::URL_TEMPLATE;
        }

        $this->urlTemplate = $urlTemplate;
    }

    /**
     * @param array $originData
     * @return string
     */
    public function render(array $originData): string
    {
        $url = $this->urlTemplate;
        foreach ($originData as $field => $value) {
            $url = str_replace('{' . $field . '}', $value, $url);
        }

        return $url;
    }
}
