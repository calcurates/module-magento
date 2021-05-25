<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\Block\Paypal\Express;

use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Calcurates\ModuleMagento\Model\Carrier\DeliveryDateFormatter;
use Calcurates\ModuleMagento\Model\Config;
use Calcurates\ModuleMagento\Model\Config\Source\DeliveryDateDisplaySource;
use Magento\Paypal\Block\Express\Review;

class ReviewPlugin
{
    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var DeliveryDateFormatter
     */
    private $formatter;

    public function __construct(
        Config $configProvider,
        DeliveryDateFormatter $formatter
    ) {
        $this->configProvider = $configProvider;
        $this->formatter = $formatter;
    }

    /**
     * Add delivery date display to shipping method on Paypal Express Checkout
     *
     * @param Review $subject
     * @param \Magento\Framework\DataObject $rate
     * @param string $format
     * @param string $inclTaxFormat
     * @return void
     * @see Review::renderShippingRateOption
     */
    public function beforeRenderShippingRateOption(
        Review $subject,
        $rate,
        $format = '%s - %s%s',
        $inclTaxFormat = ' (%s %s)'
    ): void {
        $deliveryDatesString = $this->getDeliveryDates($rate);

        if ($this->configProvider->getDeliveryDateDisplay() === DeliveryDateDisplaySource::AFTER_METHOD_NAME &&
            $deliveryDatesString
        ) {
            $rate->setMethodTitle(
                $rate->getMethodTitle() . ', ' . $deliveryDatesString
            );
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel
     *
     * @return string|null
     */
    private function getDeliveryDates($rateModel): ?string
    {
        $deliveryDatesData = $rateModel->getData(RatesResponseProcessor::CALCURATES_DELIVERY_DATES);

        $deliveryDatesString = $this->formatter->formatDeliveryDate(
            $deliveryDatesData['from'] ?? null,
            $deliveryDatesData['to'] ?? null
        );

        if (!$deliveryDatesString) {
            return null;
        }

        return $deliveryDatesString;
    }
}
