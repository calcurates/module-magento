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
            ->willReturn('dates_magento_format');

        $this->timezoneMock->expects($this->any())
            ->method('formatDateTime')
            ->willReturn($formatResult);

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

        $format = 'l (d/m/Y)';

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
                'to' => $oneDay->format(\DateTime::ATOM),
                'expectedResult' => '1 day',
            ],
            'emptyToDaysQtyOneDay' => [
                'displayType' => 'days_qty',
                'from' => $oneDay->format(\DateTime::ATOM),
                'to' => null,
                'expectedResult' => '1 day',
            ],
            'sameFromToDaysQtyOneDay' => [
                'displayType' => 'days_qty',
                'from' => $twoDays->format(\DateTime::ATOM),
                'to' => $twoDays->format(\DateTime::ATOM),
                'expectedResult' => '2 days',
            ],
            'differentFromToDaysQty' => [
                'displayType' => 'days_qty',
                'from' => $oneDay->format(\DateTime::ATOM),
                'to' => $twoDays->format(\DateTime::ATOM),
                'expectedResult' => '1-2 days',
            ],
            'emptyFromDates' => [
                'displayType' => 'dates',
                'from' => null,
                'to' => $oneDay->format(\DateTime::ATOM),
                'expectedResult' => $oneDayUtc->format($format),
            ],
            'emptyToDatesTwoDays' => [
                'displayType' => 'dates',
                'from' => null,
                'to' => $twoDays->format(\DateTime::ATOM),
                'expectedResult' => $twoDaysUtc->format($format),
            ],
            'sameFromToDatesOneDay' => [
                'displayType' => 'dates',
                'from' => $twoDays->format(\DateTime::ATOM),
                'to' => $twoDays->format(\DateTime::ATOM),
                'expectedResult' => $twoDaysUtc->format($format),
            ],
            'differentFromToDates' => [
                'displayType' => 'dates',
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
                'expectedResult' => '02-02-2020'
            ],
            'fromToDifferent' => [
                'from' => '2020-02-02',
                'to' => '2020-02-03',
                'formatResult' => '02-02-2020', // it's quite difficult to add formats to both dates, and we return single for each
                'expectedResult' => '02-02-2020 - 02-02-2020', // and check
            ],
        ];
    }
}
