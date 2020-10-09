<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\Model\Cart;

use Calcurates\ModuleMagento\Api\Data\RateDataInterface;
use Calcurates\ModuleMagento\Api\Data\RateDataInterfaceFactory;
use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Calcurates\ModuleMagento\Model\Carrier\DeliveryDateFormatter;
use Calcurates\ModuleMagento\Model\Config;
use Calcurates\ModuleMagento\Model\Config\Source\DeliveryDateDisplaySource;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use tests\unit\Magento\FunctionalTestFramework\DataGenerator\Handlers\DataObjectHandlerTest;

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
     * @var RateDataInterfaceFactory
     */
    private $rateDataFactory;

    /**
     * ShippingMethodConverterPlugin constructor.
     * @param Config $configProvider
     * @param DeliveryDateFormatter $deliveryDateFormatter
     */
    public function __construct(
        Config $configProvider,
        DeliveryDateFormatter $deliveryDateFormatter,
        RateDataInterfaceFactory $rateDataFactory
    ) {
        $this->configProvider = $configProvider;
        $this->deliveryDateFormatter = $deliveryDateFormatter;
        $this->rateDataFactory = $rateDataFactory;
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
        /** @var RateDataInterface $calcuratesRateData */
        $calcuratesRateData = $result->getExtensionAttributes()->getCalcuratesData();
        if (!$calcuratesRateData) {
            $calcuratesRateData = $this->rateDataFactory->create();
        }

        $tooltip = $rateModel->getData(RatesResponseProcessor::CALCURATES_TOOLTIP_MESSAGE);
        if ($deliveryDatesString = $this->getDeliveryDates($rateModel)) {
            switch ($this->configProvider->getDeliveryDateDisplay()) {
                case DeliveryDateDisplaySource::AFTER_METHOD_NAME:
                    $result->setMethodTitle(
                        $result->getMethodTitle() . ', ' . $deliveryDatesString
                    );
                    break;
                case DeliveryDateDisplaySource::TOOLTIP:
                    $tooltip = $deliveryDatesString;
                    break;
            }
        }

        if ($tooltip) {
            $calcuratesRateData->setTooltipMessage($tooltip);
        }

        if ($mapLink = $rateModel->getData(RatesResponseProcessor::CALCURATES_MAP_LINK)) {
            $calcuratesRateData->setMapLink($mapLink);
        }

        $result->getExtensionAttributes()->setCalcuratesData($calcuratesRateData);

        return $result;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel
     */
    private function getDeliveryDates($rateModel): ?string
    {
        $deliveryDatesData = $rateModel->getData(RatesResponseProcessor::CALCURATES_DELIVERY_DATES);

        $deliveryDatesString = $this->deliveryDateFormatter->formatDeliveryDate(
            $deliveryDatesData['from'] ?? null,
            $deliveryDatesData['to'] ?? null
        );

        if (!$deliveryDatesString) {
            return null;
        }

        return $deliveryDatesString;
    }
}
