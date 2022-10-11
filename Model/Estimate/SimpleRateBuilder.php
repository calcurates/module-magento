<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Estimate;

use Calcurates\ModuleMagento\Api\Data\SimpleRateInterface;
use Calcurates\ModuleMagento\Api\Data\SimpleRateInterfaceFactory;
use Calcurates\ModuleMagento\Model\Carrier\DeliveryDateFormatter;
use Calcurates\ModuleMagento\Model\CurrencyConverter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class SimpleRateBuilder
{
    /**
     * @var CurrencyConverter
     */
    private $currencyConverter;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var DeliveryDateFormatter
     */
    private $deliveryDateFormatter;

    /**
     * @var TemplateRenderer
     */
    private $renderer;

    /**
     * @var SimpleRateInterfaceFactory
     */
    private $simpleRateFactory;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @param CurrencyConverter $currencyConverter
     * @param PriceCurrencyInterface $priceCurrency
     * @param DeliveryDateFormatter $deliveryDateFormatter
     * @param TemplateRenderer $renderer
     * @param SimpleRateInterfaceFactory $simpleRateFactory
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        CurrencyConverter $currencyConverter,
        PriceCurrencyInterface $priceCurrency,
        DeliveryDateFormatter $deliveryDateFormatter,
        TemplateRenderer $renderer,
        SimpleRateInterfaceFactory $simpleRateFactory,
        TimezoneInterface $timezone
    ) {
        $this->currencyConverter = $currencyConverter;
        $this->priceCurrency = $priceCurrency;
        $this->deliveryDateFormatter = $deliveryDateFormatter;
        $this->renderer = $renderer;
        $this->simpleRateFactory = $simpleRateFactory;
        $this->timezone = $timezone;
    }

    /**
     * Build rate object from rate data array
     * @param array $rateData
     * @return SimpleRateInterface
     * @throws LocalizedException
     */
    public function build(array $rateData): SimpleRateInterface
    {
        $baseAmount = $this->currencyConverter->convertToBase(
            $rateData['cost'],
            $rateData['currency']
        );

        $amount = $this->priceCurrency->convertAndFormat($baseAmount, false);

        $fromDate = $toDate = '';
        if (isset($rateData['estimatedDeliveryDate'])
            && ($rateData['estimatedDeliveryDate']['from'] || $rateData['estimatedDeliveryDate']['to'])
        ) {
            [$fromDate, $toDate] = $this->deliveryDateFormatter->prepareDates(
                $rateData['estimatedDeliveryDate']['from'],
                $rateData['estimatedDeliveryDate']['to']
            );

            $fromDate = $this->deliveryDateFormatter->formatSingleDate($fromDate);
            $toDate = $this->deliveryDateFormatter->formatSingleDate($toDate);
        }

        $renderedTemplate = $this->renderer->render(
            $rateData['template'],
            [
                'rate' => $amount,
                'name' => $rateData['name'],
                'delivery_from' => $fromDate,
                'delivery_to' => $toDate
            ]
        );
        /** @var SimpleRateInterface $rate */
        $rate = $this->simpleRateFactory->create();

        $rate->setName($rateData['name']);
        $rate->setRenderedTemplate($renderedTemplate);
        $rate->setAmount($amount);
        $rate->setDeliveryDateFrom($fromDate);
        $rate->setDeliveryDateTo($toDate);
        $rate->setTemplate($rateData['template']);
        $rate->setType($rateData['type']);
        if (isset($rateData['estimatedDeliveryDate']['cutOffTime'])) {
            $localTime = $this->timezone->date();
            $localTime->setTime(
                $rateData['estimatedDeliveryDate']['cutOffTime']['hour'],
                $rateData['estimatedDeliveryDate']['cutOffTime']['minute']
            );
            $utcTime = new \DateTime(
                $this->timezone->convertConfigTimeToUtc($localTime),
                new \DateTimeZone($this->timezone->getDefaultTimezone())
            );
            $rate->setCutOffTimeHour((int)$utcTime->format('G'));
            $rate->setCutOffTimeMinute((int)$utcTime->format('i'));
        }

        return $rate;
    }
}
