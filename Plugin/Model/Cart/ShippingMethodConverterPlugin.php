<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\Model\Cart;

use Calcurates\ModuleMagento\Api\Data\MetadataInterfaceFactory;
use Calcurates\ModuleMagento\Api\Data\RateDataInterface;
use Calcurates\ModuleMagento\Api\Data\RateDataInterfaceFactory;
use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Calcurates\ModuleMagento\Client\Response\DeliveryDateProcessor;
use Calcurates\ModuleMagento\Client\Response\MetadataPoolInterface;
use Calcurates\ModuleMagento\Model\Carrier;
use Calcurates\ModuleMagento\Model\Carrier\DeliveryDateFormatter;
use Calcurates\ModuleMagento\Model\Config;
use Calcurates\ModuleMagento\Model\Config\Source\DeliveryDateDisplaySource;
use Magento\Framework\Exception\LocalizedException;
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
     * @var RateDataInterfaceFactory
     */
    private $rateDataFactory;

    /**
     * @var DeliveryDateProcessor
     */
    private $deliveryDateProcessor;

    /**
     * @var MetadataPoolInterface
     */
    private $metadataPool;

    /**
     * @var MetadataInterfaceFactory
     */
    private $metadataInterfaceFactory;

    /**
     * ShippingMethodConverterPlugin constructor.
     * @param Config $configProvider
     * @param DeliveryDateFormatter $deliveryDateFormatter
     * @param RateDataInterfaceFactory $rateDataFactory
     * @param DeliveryDateProcessor $deliveryDateProcessor
     * @param MetadataPoolInterface $metadataPool
     * @param MetadataInterfaceFactory $metadataInterfaceFactory
     */
    public function __construct(
        Config $configProvider,
        DeliveryDateFormatter $deliveryDateFormatter,
        RateDataInterfaceFactory $rateDataFactory,
        DeliveryDateProcessor $deliveryDateProcessor,
        MetadataPoolInterface $metadataPool,
        MetadataInterfaceFactory $metadataInterfaceFactory
    ) {
        $this->configProvider = $configProvider;
        $this->deliveryDateFormatter = $deliveryDateFormatter;
        $this->rateDataFactory = $rateDataFactory;
        $this->deliveryDateProcessor = $deliveryDateProcessor;
        $this->metadataPool = $metadataPool;
        $this->metadataInterfaceFactory = $metadataInterfaceFactory;
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

        $infoMessage = $rateModel->getData(RatesResponseProcessor::CALCURATES_TOOLTIP_MESSAGE);
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

        $calcuratesRateData->setDeliveryDatesList($this->getDeliveryDatesList($rateModel));

        if (isset($tooltip)) {
            $calcuratesRateData->setTooltipMessage($tooltip);
        }
        if ($infoMessage) {
            $calcuratesRateData->setInfoMessage($infoMessage);
        }

        if ($mapLink = $rateModel->getData(RatesResponseProcessor::CALCURATES_MAP_LINK)) {
            $calcuratesRateData->setMapLink($mapLink);
        }

        $isDisplayImage = $this->configProvider->isDisplayImages();
        if ($isDisplayImage && $imageUrl = $rateModel->getData(RatesResponseProcessor::CALCURATES_IMAGE_URL)) {
            $calcuratesRateData->setImageUrl($imageUrl);
        }

        if ($rateModel->getCarrier() === Carrier::CODE) {
            try {
                $metadataData = $this->metadataPool->getMetadata();
                if (!empty($metadataData)) {
                    $metadata = $this->metadataInterfaceFactory->create();
                    $metadata->setData($metadataData);
                    $calcuratesRateData->setMetadata($metadata);
                }
            } catch (LocalizedException $exception) {
                // Don't set any metadata
            }
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

    /**
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel
     * @return array
     */
    private function getDeliveryDatesList($rateModel): array
    {
        $deliveryDatesData = $rateModel->getData(RatesResponseProcessor::CALCURATES_DELIVERY_DATES);

        if (!isset($deliveryDatesData['timeSlots'])) {
            return [];
        }

        return $this->deliveryDateProcessor->getDeliveryDates($deliveryDatesData['timeSlots']);
    }
}
