<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2026 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Test\Unit\ViewModel;

use Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\ShippingRateSnapshot;
use Calcurates\ModuleMagento\Model\Config;
use Calcurates\ModuleMagento\ViewModel\OrderShippingRate;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @see OrderShippingRate
 */
class OrderShippingRateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OrderShippingRate
     */
    private $viewModel;

    protected function setUp(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('isDisplayShippingRatesMetadata')->willReturnCallback(
            function ($storeId = null) {
                return (int)$storeId === 2;
            }
        );
        $this->viewModel = new OrderShippingRate(
            $this->createStub(CurrencyInterface::class),
            $this->createStub(SerializerInterface::class),
            $this->createStub(ShippingRateSnapshot::class),
            $config
        );
    }

    /**
     * @param array $rate
     * @param bool $expected
     */
    #[DataProvider('priceFieldsDataProvider')]
    public function testHasPriceFields(array $rate, bool $expected): void
    {
        $this->assertSame($expected, $this->viewModel->hasPriceFields($rate));
    }

    /**
     * @return array
     */
    public static function priceFieldsDataProvider(): array
    {
        return [
            'empty' => [[], false],
            'base rate only' => [['base_rate' => 10], false],
            'base rate zero only' => [['base_rate' => 0], false],
            'currency and base rate' => [['currency' => 'USD', 'base_rate' => 10], false],
            'tax amount' => [['tax_amount' => 1, 'base_rate' => 10], true],
            'insurance amount' => [['insurance_amount' => 2], true],
            'surcharge' => [['surcharge' => 3], true],
            'discount' => [['discount' => 4], true],
            'surcharge rules' => [['surcharge_rules' => ['1' => 5]], true],
            'discount rules' => [['discount_rules' => ['2' => 6]], true],
            'packages' => [['packages' => [['name' => 'Box', 'qty' => 1]]], true],
            'empty extras' => [['base_rate' => 10, 'surcharge_rules' => [], 'packages' => []], false],
        ];
    }

    public function testIsDisplayShippingRatesMetadataUsesStoreConfig(): void
    {
        $this->assertFalse($this->viewModel->isDisplayShippingRatesMetadata(1));
        $this->assertTrue($this->viewModel->isDisplayShippingRatesMetadata(2));
    }
}
