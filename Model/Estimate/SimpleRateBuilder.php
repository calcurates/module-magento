<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Estimate;

use Calcurates\ModuleMagento\Api\Data\SimpleRateInterface;
use Calcurates\ModuleMagento\Api\Data\SimpleRateInterfaceFactory;
use Calcurates\ModuleMagento\Model\Carrier\DeliveryDateFormatter;
use Calcurates\ModuleMagento\Model\CurrencyConverter;
use Magento\Framework\Pricing\PriceCurrencyInterface;

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

    public function __construct(
        CurrencyConverter $currencyConverter,
        PriceCurrencyInterface $priceCurrency,
        DeliveryDateFormatter $deliveryDateFormatter,
        TemplateRenderer $renderer,
        SimpleRateInterfaceFactory $simpleRateFactory
    ) {
        $this->currencyConverter = $currencyConverter;
        $this->priceCurrency = $priceCurrency;
        $this->deliveryDateFormatter = $deliveryDateFormatter;
        $this->renderer = $renderer;
        $this->simpleRateFactory = $simpleRateFactory;
    }

    public function build(array $rateData): SimpleRateInterface
    {
        $baseAmount = $this->currencyConverter->convertToBase(
            $rateData['cost'],
            $rateData['currency']
        );

        $amount = $this->priceCurrency->convertAndFormat($baseAmount, false);

        $fromDate = $toDate = '';
        if ($rateData['estimatedDeliveryDate']['from'] || $rateData['estimatedDeliveryDate']['to']) {
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

        return $rate;
    }
}
