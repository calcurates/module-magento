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
    public const URL_TEMPLATE = 'https://www.google.com/maps/search/'
        . '?api=1&query={latitude},{longitude}&query_place_id={googlePlaceId}';

    /**
     * @param array $originData
     * @return string
     */
    public function render(array $originData): string
    {
        return str_replace(
            [
                '{latitude}',
                '{longitude}',
                '{googlePlaceId}',
            ],
            [
                $originData['latitude'],
                $originData['longitude'],
                $originData['googlePlaceId'],
            ],
            self::URL_TEMPLATE
        );
    }
}
