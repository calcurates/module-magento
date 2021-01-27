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
    const DATE_FORMAT = 'l (d/m/Y)';

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
     * @return string
     */
    public function formatDeliveryDate(?string $fromString, ?string $toString): string
    {
        if (!$fromString && !$toString) {
            return '';
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

        $datesDisplayType = $this->configProvider->getDeliveryDateDisplayType();

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
     * @param \DateTime $from
     * @param \DateTime $to
     * @return string
     */
    private function formatDays(\DateTime $from, \DateTime $to): string
    {
        $current = new \DateTime('now', $from->getTimezone());

        $diffDaysFrom = abs(ceil(($from->getTimestamp() - $current->getTimestamp()) / (60*60*24)));
        $diffDaysTo = abs(ceil(($to->getTimestamp() - $current->getTimestamp()) / (60*60*24)));

        if ($diffDaysFrom === $diffDaysTo) {
            return $diffDaysFrom === 1.0 ? (string)__('%1 day', $diffDaysFrom) : (string)__('%1 days', $diffDaysFrom);
        }

        return (string)__('%1-%2 days', $diffDaysFrom, $diffDaysTo);
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return string
     */
    private function formatDates(\DateTime $from, \DateTime $to): string
    {
        $formattedFrom = $from->format(static::DATE_FORMAT);
        $formattedTo = $to->format(static::DATE_FORMAT);

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
        $formattedFrom = $this->timezone->formatDateTime($from);
        $formattedTo = $this->timezone->formatDateTime($to);

        if ($formattedFrom === $formattedTo) {
            return $formattedFrom;
        }

        return $formattedFrom . ' - ' .  $formattedTo;
    }
}
