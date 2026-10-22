<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2026 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Test\Unit\Model\Cart\Shipping\Rate\InfoMessage;

use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\InfoMessage\RatePrice;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @see RatePrice
 */
class RatePriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PriceCurrencyInterface|\PHPUnit\Framework\MockObject\Stub
     */
    private $priceCurrency;

    /**
     * @var RatePrice
     */
    private $ratePrice;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->priceCurrency = $this->createStub(PriceCurrencyInterface::class);
        $this->priceCurrency->method('convertAndFormat')->willReturnCallback(
            function ($amount, $includeContainer, $precision, $store, $currency) {
                return $amount . ' ' . $currency;
            }
        );
        $this->ratePrice = $objectManager->getObject(RatePrice::class, [
            'priceCurrency' => $this->priceCurrency,
        ]);
    }

    /**
     * @param mixed $amount
     * @param string $expected
     */
    #[DataProvider('numericInsuranceDataProvider')]
    public function testProcessReplacesNumericInsurance($amount, string $expected): void
    {
        $rate = new DataObject([
            RatesResponseProcessor::CALCURATES_CURRENCY => 'USD',
            RatesResponseProcessor::CALCURATES_INSURANCE_AMOUNT => $amount,
        ]);

        $this->assertSame(
            $expected,
            $this->ratePrice->process($rate, 'Insured for {insurance_amount}')
        );
    }

    /**
     * @return array
     */
    public static function numericInsuranceDataProvider(): array
    {
        return [
            'number' => [5, 'Insured for 5 USD'],
            'zero' => [0, 'Insured for 0 USD'],
            'numeric string' => ['2.5', 'Insured for 2.5 USD'],
        ];
    }

    /**
     * @param array $rateData
     */
    #[DataProvider('ignoredInsuranceDataProvider')]
    public function testProcessKeepsPlaceholderWhenInsuranceCannotBeFormatted(array $rateData): void
    {
        $result = $this->ratePrice->process(
            new DataObject($rateData),
            'Insured for {insurance_amount}'
        );

        $this->assertSame('Insured for {insurance_amount}', $result);
    }

    /**
     * @return array
     */
    public static function ignoredInsuranceDataProvider(): array
    {
        return [
            'null amount' => [[
                RatesResponseProcessor::CALCURATES_CURRENCY => 'USD',
                RatesResponseProcessor::CALCURATES_INSURANCE_AMOUNT => null,
            ]],
            'empty string' => [[
                RatesResponseProcessor::CALCURATES_CURRENCY => 'USD',
                RatesResponseProcessor::CALCURATES_INSURANCE_AMOUNT => '',
            ]],
            'non numeric' => [[
                RatesResponseProcessor::CALCURATES_CURRENCY => 'USD',
                RatesResponseProcessor::CALCURATES_INSURANCE_AMOUNT => 'n/a',
            ]],
            'missing amount' => [[
                RatesResponseProcessor::CALCURATES_CURRENCY => 'USD',
            ]],
            'missing currency' => [[
                RatesResponseProcessor::CALCURATES_INSURANCE_AMOUNT => 5,
            ]],
        ];
    }
}
