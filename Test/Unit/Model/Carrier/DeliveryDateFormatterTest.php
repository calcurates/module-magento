<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Test\Unit\Model\Carrier;

use Calcurates\ModuleMagento\Model\Carrier\DeliveryDateFormatter;
use Calcurates\ModuleMagento\Model\Config;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * @see DeliveryDateFormatter
 */
class DeliveryDateFormatterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DeliveryDateFormatter
     */
    private $model;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configProviderMock;

    public function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->configProviderMock = $this->createMock(Config::class);
        $timezoneMock = $this->createMock(TimezoneInterface::class);
        $timezoneMock->expects($this->any())
            ->method('getConfigTimezone')
            ->willReturn('UTC');

        $this->model = $objectManager->getObject(
            DeliveryDateFormatter::class,
            [
                'configProvider' => $this->configProviderMock,
                'timezone' => $timezoneMock,
            ]
        );
    }

    /**
     * @covers \Calcurates\ModuleMagento\Model\Carrier\DeliveryDateFormatter::formatDeliveryDate
     * @dataProvider dataProvider
     * @param string $displayType
     * @param string|null $from
     * @param string|null $to
     * @param string $expectedResult
     */
    public function testFormatDeliveryDate(
        string $displayType,
        ?string $from,
        ?string $to,
        string $expectedResult
    ) {
        $this->configProviderMock->expects($this->any())
            ->method('getDeliveryDateDisplayType')
            ->willReturn($displayType);

        $actualResult = $this->model->formatDeliveryDate($from, $to);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function dataProvider(): array
    {
        $timezone = new \DateTimeZone('Europe/Minsk');
        $oneDay = new \DateTime('+ 1 day', $timezone);
        $oneDayUtc = clone $oneDay;
        $oneDayUtc->setTimezone(new \DateTimeZone('UTC'));
        $twoDays = new \DateTime('+2 days', $timezone);

        $twoDaysUtc = clone $twoDays;
        $twoDaysUtc->setTimezone(new \DateTimeZone('UTC'));

        $format = '%d/%m/%Y (%l)';

        return [
            'emptyDates' => [
                'displayType' => 'days_qty',
                'from' => null,
                'to' => null,
                'expectedResult' => '',
            ],
            'emptyFromDaysQtyOneDay' => [
                'displayType' => 'days_qty',
                'from' => null,
                'to' => $oneDay->format(\DateTimeInterface::ATOM),
                'expectedResult' => '1 day',
            ],
            'emptyToDaysQtyOneDay' => [
                'displayType' => 'days_qty',
                'from' => $oneDay->format(\DateTimeInterface::ATOM),
                'to' => null,
                'expectedResult' => '1 day',
            ],
            'sameFromToDaysQtyOneDay' => [
                'displayType' => 'days_qty',
                'from' => $twoDays->format(\DateTimeInterface::ATOM),
                'to' => $twoDays->format(\DateTimeInterface::ATOM),
                'expectedResult' => '2 days',
            ],
            'differentFromToDaysQty' => [
                'displayType' => 'days_qty',
                'from' => $oneDay->format(\DateTimeInterface::ATOM),
                'to' => $twoDays->format(\DateTimeInterface::ATOM),
                'expectedResult' => '1-2 days',
            ],
            'emptyFromDates' => [
                'displayType' => 'dates',
                'from' => null,
                'to' => $oneDay->format(\DateTimeInterface::ATOM),
                'expectedResult' => $oneDayUtc->format($format),
            ],
            'emptyToDatesTwoDays' => [
                'displayType' => 'dates',
                'from' => null,
                'to' => $twoDays->format(\DateTimeInterface::ATOM),
                'expectedResult' => $twoDaysUtc->format($format),
            ],
            'sameFromToDatesOneDay' => [
                'displayType' => 'dates',
                'from' => $twoDays->format(\DateTimeInterface::ATOM),
                'to' => $twoDays->format(\DateTimeInterface::ATOM),
                'expectedResult' => $twoDaysUtc->format($format),
            ],
            'differentFromToDates' => [
                'displayType' => 'dates',
                'from' => $oneDay->format(\DateTimeInterface::ATOM),
                'to' => $twoDays->format(\DateTimeInterface::ATOM),
                'expectedResult' => $oneDayUtc->format($format) . ' - ' . $twoDaysUtc->format($format),
            ],
        ];
    }
}
