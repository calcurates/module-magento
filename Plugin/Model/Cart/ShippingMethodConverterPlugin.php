<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\Model\Cart;

use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Calcurates\ModuleMagento\Model\Carrier\DeliveryDateFormatter;
use Calcurates\ModuleMagento\Model\Config;
use Calcurates\ModuleMagento\Model\Config\Source\DeliveryDateDisplaySource;
use Magento\Quote\Model\Cart\ShippingMethodConverter;

class ShippingMethodConverterPlugin
{
    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var DeliveryDateFormatter
     */
    private $deliveryDateFormatter;

    /**
     * ShippingMethodConverterPlugin constructor.
     * @param Config $configProvider
     * @param DeliveryDateFormatter $deliveryDateFormatter
     */
    public function __construct(Config $configProvider, DeliveryDateFormatter $deliveryDateFormatter)
    {
        $this->configProvider = $configProvider;
        $this->deliveryDateFormatter = $deliveryDateFormatter;
    }

    /**
     * @param ShippingMethodConverter $subject
     * @param \Magento\Quote\Api\Data\ShippingMethodInterface  $result
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel The rate model.
     * @param string $quoteCurrencyCode The quote currency code.
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface
     */
    public function afterModelToDataObject(ShippingMethodConverter $subject, $result, $rateModel, $quoteCurrencyCode)
    {
        $tooltip = $rateModel->getData(RatesResponseProcessor::CALCURATES_TOOLTIP_MESSAGE);
        if ($tooltip) {
            $result->getExtensionAttributes()->setCalcuratesTooltip($tooltip);
        }

        if ($this->configProvider->getDeliveryDateDisplay() === DeliveryDateDisplaySource::DO_NOT_SHOW) {
            return $result;
        }

        $this->addDeliveryDates($rateModel, $result);

        return $result;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel
     * @param \Magento\Quote\Api\Data\ShippingMethodInterface $shippingMethod
     */
    private function addDeliveryDates($rateModel, $shippingMethod)
    {
        $deliveryDatesData = $rateModel->getData(RatesResponseProcessor::CALCURATES_DELIVERY_DATES);

        $deliveryDatesString = $this->deliveryDateFormatter->formatDeliveryDate(
            $deliveryDatesData['from'] ?? null,
            $deliveryDatesData['to'] ?? null
        );

        if (!$deliveryDatesString) {
            return;
        }

        if ($this->configProvider->getDeliveryDateDisplay() === DeliveryDateDisplaySource::AFTER_METHOD_NAME) {
            $shippingMethod->setMethodTitle(
                $shippingMethod->getMethodTitle() . ', ' . $deliveryDatesString
            );

            return;
        }

        $shippingMethod->getExtensionAttributes()->setCalcuratesTooltip($deliveryDatesString);
    }
}
