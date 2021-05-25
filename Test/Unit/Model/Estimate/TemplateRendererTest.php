<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Test\Unit\Model\Estimate;

use Calcurates\ModuleMagento\Model\Estimate\TemplateRenderer;

class TemplateRendererTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Calcurates\ModuleMagento\Model\Estimate\TemplateRenderer::render
     * @dataProvider dataProvider
     *
     * @param string $template
     * @param array $variables
     * @param string $result
     */
    public function testRender(string $template, array $variables, string $result)
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectManager->getObject(TemplateRenderer::class);

        $actualResult = $model->render($template, $variables);

        self::assertSame($result, $actualResult);
    }

    /**
     * @return array[]
     */
    public function dataProvider(): array
    {
        return [
            'emptyStringEmptyVars' => [
                'template' => '',
                'variables' => [],
                'result' => '',
            ],
            'emptyVariables' => [
                'template' => 'ASfdasfsaf {var} {var2}',
                'variables' => [],
                'result' => 'ASfdasfsaf {var} {var2}',
            ],
            'emptyTemplateManyVariables' => [
                'template' => '',
                'variables' => ['var1' => '1', 'var2' => 2],
                'result' => '',
            ],
            'correctRender' => [
                'template' => 'Abbbs {var1} fewfwef{var2} color is {var3} {var3}',
                'variables' => ['var1' => '1', 'var2' => 2, 'var3' => 'fffff'],
                'result' => 'Abbbs 1 fewfwef2 color is fffff fffff',
            ]
        ];
    }
}
