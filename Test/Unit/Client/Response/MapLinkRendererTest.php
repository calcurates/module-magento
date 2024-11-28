<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Test\Unit\Client\Response;

use Calcurates\ModuleMagento\Client\Response\MapLinkRenderer;

/**
 * @see MapLinkRenderer
 */
class MapLinkRendererTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Calcurates\ModuleMagento\Client\Response\MapLinkRenderer::render
     * @dataProvider dataProvider
     * @param array $originData
     * @param string $expectedResult
     */
    public function testRender(array $originData, string $expectedResult)
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var MapLinkRenderer $model */
        $model = $objectManager->getObject(MapLinkRenderer::class);

        $actualResult = $model->render($originData);

        $this->assertSame($expectedResult, $actualResult);
    }

    public function dataProvider(): array
    {
        return [
            'full' => [
                'originData' => [
                    'latitude' => 2.521,
                    'longitude' => 29.321,
                    'googlePlaceId' => 'test'
                ],
                'expectedResult' => 'https://www.google.com/maps/search/'
                . '?api=1&query=2.521,29.321&query_place_id=test'
            ],
            'partial' => [
                'originData' => [
                    'latitude' => 55,
                    'longitude' => 66,
                    'googlePlaceId' => null
                ],
                'expectedResult' => 'https://www.google.com/maps/search/'
                    . '?api=1&query=55,66&query_place_id='
            ],
        ];
    }
}
