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
use Magento\Framework\DataObject;
use Magento\Paypal\Block\Express\Review;
use Magento\Quote\Model\Quote\Address\Rate;

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
     * Do not show price for MetaRate method
     *
     * @param Review $subject
     * @param DataObject $rate
     * @param string $format
     * @param string $inclTaxFormat
     * @return array
     * @see Review::renderShippingRateOption
     */
    public function beforeRenderShippingRateOption(
        Review $subject,
        $rate,
        $format = '%s - %s%s',
        $inclTaxFormat = ' (%s %s)'
    ): array {
        $deliveryDatesString = $this->getDeliveryDates($rate);

        if ($this->configProvider->getDeliveryDateDisplay() !== DeliveryDateDisplaySource::DO_NOT_SHOW &&
            $deliveryDatesString
        ) {
            $rate->setMethodTitle(
                $rate->getMethodTitle() . ', ' . $deliveryDatesString
            );
        }
        if ($rate->getCode() === 'calcurates_metarate') {
            $rate->setMethodTitle($this->configProvider->getSplitCheckoutTitle());
            $format = '%s';
        }
        return [$rate, $format, $inclTaxFormat];
    }

    /**
     * @param Rate $rateModel
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
