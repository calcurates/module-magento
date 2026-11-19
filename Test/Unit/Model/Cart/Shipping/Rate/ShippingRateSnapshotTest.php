<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2026 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Test\Unit\Model\Cart\Shipping\Rate;

use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\ShippingRateSnapshot;
use Magento\Framework\DataObject;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @see ShippingRateSnapshot
 */
class ShippingRateSnapshotTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ShippingRateSnapshot
     */
    private $snapshot;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->snapshot = $objectManager->getObject(ShippingRateSnapshot::class);
    }

    /**
     * @param mixed $amount
     * @param float|null $expected
     */
    #[DataProvider('normalizeAmountDataProvider')]
    public function testNormalizeAmount($amount, ?float $expected): void
    {
        $this->assertSame($expected, $this->snapshot->normalizeAmount($amount));
    }

    /**
     * @return array
     */
    public static function normalizeAmountDataProvider(): array
    {
        return [
            'integer' => [5, 5.0],
            'float' => [5.5, 5.5],
            'zero' => [0, 0.0],
            'numeric string' => ['2.50', 2.5],
            'null' => [null, null],
            'empty string' => ['', null],
            'non numeric string' => ['n/a', null],
            'array' => [['amount' => 4], null],
        ];
    }

    /**
     * @param mixed $insurance
     * @param bool $setInsurance
     * @param float|null $expected
     */
    #[DataProvider('insuranceSnapshotDataProvider')]
    public function testFromRateStoresNumericInsurance($insurance, bool $setInsurance, ?float $expected): void
    {
        $data = [
            RatesResponseProcessor::CALCURATES_CURRENCY => 'USD',
            RatesResponseProcessor::CALCURATES_FINAL_RATE => 99,
        ];
        if ($setInsurance) {
            $data[RatesResponseProcessor::CALCURATES_INSURANCE_AMOUNT] = $insurance;
        }

        $snapshot = $this->snapshot->fromRate(new DataObject($data));

        $this->assertSame('USD', $snapshot['currency']);
        $this->assertArrayNotHasKey('final_rate', $snapshot);
        if ($expected === null) {
            $this->assertArrayNotHasKey('insurance_amount', $snapshot);
            return;
        }

        $this->assertSame($expected, $snapshot['insurance_amount']);
    }

    /**
     * @return array
     */
    public static function insuranceSnapshotDataProvider(): array
    {
        return [
            'number' => [8.75, true, 8.75],
            'zero' => [0, true, 0.0],
            'numeric string' => ['1.5', true, 1.5],
            'null' => [null, true, null],
            'empty string' => ['', true, null],
            'non numeric' => ['n/a', true, null],
            'missing' => [null, false, null],
        ];
    }
}
