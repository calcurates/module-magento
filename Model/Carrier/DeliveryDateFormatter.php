<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Carrier;

use Calcurates\ModuleMagento\Model\Config;
use Calcurates\ModuleMagento\Model\Config\Source\DeliveryDateDisplayTypeSource;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;

class DeliveryDateFormatter
{
    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * DeliveryDateFormatter constructor.
     * @param Config $configProvider
     * @param TimezoneInterface $timezone
     */
    public function __construct(Config $configProvider, TimezoneInterface $timezone)
    {
        $this->configProvider = $configProvider;
        $this->timezone = $timezone;
    }

    /**
     * @param string|null $fromString
     * @param string|null $toString
     * @return array(\DateTime|null, \DateTime|null) Prepared dates
     */
    public function prepareDates(?string $fromString, ?string $toString): array
    {
        if (!$fromString && !$toString) {
            return [null, null];
        }

        if ($fromString) {
            $from = new \DateTime($fromString);
        } else {
            $from = new \DateTime($toString);
        }

        if ($toString) {
            $to = new \DateTime($toString);
        } else {
            $to = clone $from;
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $timezoneString = $this->timezone->getConfigTimezone(ScopeInterface::SCOPE_STORES);
        $timezone = new \DateTimeZone($timezoneString);
        $from->setTimezone($timezone);
        $to->setTimezone($timezone);

        return [$from, $to];
    }

    /**
     * @param string|null $date
     * @return \DateTimeZone|null
     */
    public function prepareDate(?string $date): ?\DateTime
    {
        if (!$date) {
            return null;
        }
        $date = new \DateTime($date);
        $timezoneString = $this->timezone->getConfigTimezone(ScopeInterface::SCOPE_STORES);
        $timezone = new \DateTimeZone($timezoneString);
        $date->setTimezone($timezone);
        return $date;
    }

    /**
     * @param \DateTime $dateTime
     * @param string|null $datesDisplayType
     * @return string
     */
    public function formatSingleDate(\DateTime $dateTime, ?string $datesDisplayType = null): string
    {
        $datesDisplayType = $datesDisplayType ?? $this->configProvider->getDeliveryDateDisplayType();

        switch ($datesDisplayType) {
            case DeliveryDateDisplayTypeSource::DAYS_QTY:
                $value = $this->formatDay($dateTime);
                break;
            case DeliveryDateDisplayTypeSource::DATES_MAGENTO_FORMAT:
                $value = $this->formatDateMagentoLocale($dateTime);
                break;
            case DeliveryDateDisplayTypeSource::DATES:
            default:
                $value = $this->formatDate($dateTime);
                break;
        }

        return $value;
    }

    /**
     * @param string|null $fromString
     * @param string|null $toString
     * @param string|null $datesDisplayType
     * @return string
     */
    public function formatDeliveryDate(?string $fromString, ?string $toString, ?string $datesDisplayType = null): string
    {
        if (!$fromString && !$toString) {
            return '';
        }

        [$from, $to] = $this->prepareDates($fromString, $toString);

        $datesDisplayType = $datesDisplayType ?? $this->configProvider->getDeliveryDateDisplayType();

        switch ($datesDisplayType) {
            case DeliveryDateDisplayTypeSource::DAYS_QTY:
                $value = $this->formatDays($from, $to);
                break;
            case DeliveryDateDisplayTypeSource::DATES_MAGENTO_FORMAT:
                $value = $this->formatDatesMagentoLocale($from, $to);
                break;
            case DeliveryDateDisplayTypeSource::DATES:
            default:
                $value = $this->formatDates($from, $to);
                break;
        }

        return $value;
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $date
     * @return string
     * @throws \Exception
     */
    public function formatTimeInterval(string $from, string $to, string $date = '2020-02-02'): string
    {
        $from = new \DateTime($date . ' ' . $from);
        $to = new \DateTime($date . ' ' . $to);

        return $from->format('H:i') . ' - ' . $to->format('H:i');
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return string
     */
    private function formatDays(\DateTime $from, \DateTime $to): string
    {
        $diffDaysFrom = $this->getDiffDays($from);
        $diffDaysTo = $this->getDiffDays($to);

        if ($diffDaysFrom === $diffDaysTo) {
            return $diffDaysFrom === 1 ? (string)__('%1 day', $diffDaysFrom) : (string)__('%1 days', $diffDaysFrom);
        }

        return (string)__('%1-%2 days', $diffDaysFrom, $diffDaysTo);
    }

    /**
     * @param \DateTime $date
     * @return string
     */
    private function formatDay(\DateTime $date): string
    {
        $diffDays = $this->getDiffDays($date);

        return $diffDays === 1 ? (string)__('%1 day', $diffDays) : (string)__('%1 days', $diffDays);
    }

    /**
     * @param \DateTime $dateTime
     * @return string
     */
    private function formatDate(\DateTime $dateTime): string
    {
        return $this->timezone->formatDateTime(
            $dateTime,
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::FULL,
            null,
            null,
            $this->configProvider->getDeliveryDateDisplayFormat()
        );
    }

    /**
     * Hardcoded short day name before formatted date
     * @param \DateTime $dateTime
     * @return string
     */
    private function formatDateMagentoLocale(\DateTime $dateTime): string
    {
        $prefix = $dateTime->format('D') . ' ';

        return $prefix . $this->timezone->formatDateTime(
            $dateTime,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE
        );
    }

    /**
     * @param \DateTime $dateTime
     * @return int
     */
    private function getDiffDays(\DateTime $dateTime): int
    {
        $current = new \DateTime('now', $dateTime->getTimezone());

        return (int)abs(ceil(($dateTime->getTimestamp() - $current->getTimestamp()) / (60 * 60 * 24)));
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return string
     */
    private function formatDates(\DateTime $from, \DateTime $to): string
    {
        $formattedFrom = $this->formatDate($from);
        $formattedTo = $this->formatDate($to);

        if ($formattedFrom === $formattedTo) {
            return $formattedFrom;
        }

        return $formattedFrom . ' - ' . $formattedTo;
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return string
     */
    private function formatDatesMagentoLocale(\DateTime $from, \DateTime $to): string
    {
        $formattedFrom = $this->formatDateMagentoLocale($from);
        $formattedTo = $this->formatDateMagentoLocale($to);

        if ($formattedFrom === $formattedTo) {
            return $formattedFrom;
        }

        return $formattedFrom . ' - ' . $formattedTo;
    }
}
