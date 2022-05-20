<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Test\Unit\Model\Carrier;

use Amasty\RecurringPayments\Model\Subscription\Scheduler\DateTimeInterval;
use Calcurates\ModuleMagento\Model\Carrier\DeliveryDateFormatter;
use Calcurates\ModuleMagento\Model\Config;
use Calcurates\ModuleMagento\Model\Config\Source\DeliveryDateDisplayTypeSource;
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

    /**
     * @var TimezoneInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $timezoneMock;

    public function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->configProviderMock = $this->createMock(Config::class);
        $this->timezoneMock = $this->createMock(TimezoneInterface::class);
        $this->timezoneMock->expects($this->any())
            ->method('getConfigTimezone')
            ->willReturn('UTC');

        $this->model = $objectManager->getObject(
            DeliveryDateFormatter::class,
            [
                'configProvider' => $this->configProviderMock,
                'timezone' => $this->timezoneMock,
            ]
        );
    }

    /**
     * @covers       \Calcurates\ModuleMagento\Model\Carrier\DeliveryDateFormatter::formatDeliveryDate
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
     * @covers       \Calcurates\ModuleMagento\Model\Carrier\DeliveryDateFormatter::formatDeliveryDate
     * @dataProvider dataProviderForMagentoLocale
     * @param string $from
     * @param string $to
     * @param string $formatResult
     * @param string $expectedResult
     */
    public function testFormatDeliveryDateMagentoLocale(
        string $from,
        string $to,
        string $formatResult,
        string $expectedResult
    ) {
        $this->configProviderMock->expects($this->any())
            ->method('getDeliveryDateDisplayType')
            ->willReturn(DeliveryDateDisplayTypeSource::DATES_MAGENTO_FORMAT);

        $this->timezoneMock->expects($this->any())
            ->method('formatDateTime')
            ->willReturn($formatResult);

        $actualResult = $this->model->formatDeliveryDate($from, $to);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * Test format single date to days
     * @covers \Calcurates\ModuleMagento\Model\Carrier\DeliveryDateFormatter::formatSingleDate
     */
    public function testFormatSingleDateDays()
    {
        $this->configProviderMock
            ->method('getDeliveryDateDisplayType')
            ->willReturn(DeliveryDateDisplayTypeSource::DAYS_QTY);
        $date = new \DateTime('now');

        $date->add(new \DateInterval('P1D'));
        $this->assertSame('1 day', $this->model->formatSingleDate($date));

        $date->add(new \DateInterval('P1D'));
        $this->assertSame('2 days', $this->model->formatSingleDate($date));

        $date->add(new \DateInterval('P5D'));
        $this->assertSame('7 days', $this->model->formatSingleDate($date));

        // test up rounding 7.001 is also 8 days
        $date->add(new \DateInterval('PT60S'));
        $this->assertSame('8 days', $this->model->formatSingleDate($date));
    }

    /**
     * Test format single date to magento format
     * @covers \Calcurates\ModuleMagento\Model\Carrier\DeliveryDateFormatter::formatSingleDate
     */
    public function testFormatSingleDateMagentoLocale()
    {
        $formattedResult = '20.02.2021';
        $expectedResult = 'Sat ' . $formattedResult;

        $this->configProviderMock
            ->method('getDeliveryDateDisplayType')
            ->willReturn(DeliveryDateDisplayTypeSource::DATES_MAGENTO_FORMAT);

        $this->timezoneMock
            ->method('formatDateTime')
            ->willReturn($formattedResult);

        $dateTime = new \DateTime('2021-02-20');

        $result = $this->model->formatSingleDate($dateTime);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Test format single date to default
     * @covers \Calcurates\ModuleMagento\Model\Carrier\DeliveryDateFormatter::formatSingleDate
     */
    public function testFormatSingleDateDefaultFormat()
    {
        $this->configProviderMock
            ->method('getDeliveryDateDisplayType')
            ->willReturn(DeliveryDateDisplayTypeSource::DATES);

        $dateTime = new \DateTime('2021-02-20');

        $result = $this->model->formatSingleDate($dateTime);

        $this->assertSame($dateTime->format(DeliveryDateFormatter::DATE_FORMAT), $result);
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $date
     * @param string $expectedResult
     * @return void
     * @throws \Exception
     * @dataProvider dataProviderForTimeInterval
     */
    public function testFormatTimeInterval($from, $to, $date, $expectedResult)
    {
        $result = $this->model->formatTimeInterval($from, $to, $date);

        $this->assertSame($expectedResult, $result);
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

        $format = 'l (d/m/Y)';

        return [
            'emptyDates' => [
                'displayType' => DeliveryDateDisplayTypeSource::DAYS_QTY,
                'from' => null,
                'to' => null,
                'expectedResult' => '',
            ],
            'emptyFromDaysQtyOneDay' => [
                'displayType' => DeliveryDateDisplayTypeSource::DAYS_QTY,
                'from' => null,
                'to' => $oneDay->format(\DateTime::ATOM),
                'expectedResult' => '1 day',
            ],
            'emptyToDaysQtyOneDay' => [
                'displayType' => DeliveryDateDisplayTypeSource::DAYS_QTY,
                'from' => $oneDay->format(\DateTime::ATOM),
                'to' => null,
                'expectedResult' => '1 day',
            ],
            'sameFromToDaysQtyOneDay' => [
                'displayType' => DeliveryDateDisplayTypeSource::DAYS_QTY,
                'from' => $twoDays->format(\DateTime::ATOM),
                'to' => $twoDays->format(\DateTime::ATOM),
                'expectedResult' => '2 days',
            ],
            'differentFromToDaysQty' => [
                'displayType' => DeliveryDateDisplayTypeSource::DAYS_QTY,
                'from' => $oneDay->format(\DateTime::ATOM),
                'to' => $twoDays->format(\DateTime::ATOM),
                'expectedResult' => '1-2 days',
            ],
            'emptyFromDates' => [
                'displayType' => DeliveryDateDisplayTypeSource::DATES,
                'from' => null,
                'to' => $oneDay->format(\DateTime::ATOM),
                'expectedResult' => $oneDayUtc->format($format),
            ],
            'emptyToDatesTwoDays' => [
                'displayType' => DeliveryDateDisplayTypeSource::DATES,
                'from' => null,
                'to' => $twoDays->format(\DateTime::ATOM),
                'expectedResult' => $twoDaysUtc->format($format),
            ],
            'sameFromToDatesOneDay' => [
                'displayType' => DeliveryDateDisplayTypeSource::DATES,
                'from' => $twoDays->format(\DateTime::ATOM),
                'to' => $twoDays->format(\DateTime::ATOM),
                'expectedResult' => $twoDaysUtc->format($format),
            ],
            'differentFromToDates' => [
                'displayType' => DeliveryDateDisplayTypeSource::DATES,
                'from' => $oneDay->format(\DateTime::ATOM),
                'to' => $twoDays->format(\DateTime::ATOM),
                'expectedResult' => $oneDayUtc->format($format) . ' - ' . $twoDaysUtc->format($format),
            ],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderForMagentoLocale(): array
    {
        return [
            'fromToEqual' => [
                'from' => '2020-02-02',
                'to' => '2020-02-02',
                'formatResult' => '02-02-2020',
                'expectedResult' => 'Sun 02-02-2020'
            ],
            'fromToDifferent' => [
                'from' => '2020-02-02',
                'to' => '2020-02-03',
                'formatResult' => '02-02-2020', //fixme: it's quite difficult to add formats to both dates, and we return single for each
                'expectedResult' => 'Sun 02-02-2020 - Mon 02-02-2020', // and check
            ],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderForTimeInterval(): array
    {
        return [
            'format' => [
                'from' => '00:00:00',
                'to' => '12:00:00',
                'date' => '2020-02-02',
                '00:00 - 12:00'
            ],
            'format24' => [
                'from' => '14:34:12',
                'to' => '15:25:11',
                'date' => '2020-02-02',
                '14:34 - 15:25'
            ],
        ];
    }
}
