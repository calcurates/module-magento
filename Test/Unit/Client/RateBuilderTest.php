<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2026 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Test\Unit\Client;

use Calcurates\ModuleMagento\Client\RateBuilder;
use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Calcurates\ModuleMagento\Model\Config;
use Calcurates\ModuleMagento\Model\Config\Source\RateTaxDisplaySource;
use Calcurates\ModuleMagento\Model\CurrencyConverter;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @see RateBuilder
 */
class RateBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RateBuilder
     */
    private $builder;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $config = $this->createStub(Config::class);
        $config->method('getRatesTaxDisplayType')->willReturn(RateTaxDisplaySource::TAX_EXCLUDED);
        $config->method('isDisplayShippingOptionName')->willReturn(false);

        $currencyConverter = $this->createStub(CurrencyConverter::class);
        $currencyConverter->method('convertToBase')->willReturnArgument(0);

        $priceCurrency = $this->createStub(PriceCurrencyInterface::class);
        $priceCurrency->method('round')->willReturnArgument(0);

        $methodFactory = $this->createStub(MethodFactory::class);
        $methodFactory->method('create')->willReturnCallback(function () use ($objectManager, $priceCurrency) {
            return $objectManager->getObject(Method::class, ['priceCurrency' => $priceCurrency]);
        });

        $this->builder = $objectManager->getObject(RateBuilder::class, [
            'rateMethodFactory' => $methodFactory,
            'currencyConverter' => $currencyConverter,
            'calcuratesConfig' => $config,
        ]);
    }

    /**
     * @param array $insuranceData
     * @param float|null $expected
     */
    #[DataProvider('insuranceDataProvider')]
    public function testBuildReadsInsuranceAmount(array $insuranceData, ?float $expected): void
    {
        $rates = $this->builder->build('flatRate_1', [
            'name' => 'Flat Rate',
            'rate' => array_merge(
                [
                    'cost' => 10,
                    'currency' => 'USD',
                ],
                $insuranceData
            ),
        ]);

        $this->assertCount(1, $rates);
        $stored = $rates[0]->getData(RatesResponseProcessor::CALCURATES_INSURANCE_AMOUNT);
        if ($expected === null) {
            $this->assertNull($stored);
            return;
        }

        $this->assertSame($expected, $stored);
    }

    /**
     * @return array
     */
    public static function insuranceDataProvider(): array
    {
        return [
            'number' => [['insurance' => 12.5], 12.5],
            'zero' => [['insurance' => 0], 0.0],
            'numeric string' => [['insurance' => '3.25'], 3.25],
            'null' => [['insurance' => null], null],
            'empty string' => [['insurance' => ''], null],
            'non numeric string' => [['insurance' => 'n/a'], null],
            'missing key' => [[], null],
        ];
    }
}
