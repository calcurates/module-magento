<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response;

use Calcurates\ModuleMagento\Api\Data\DeliveryDate\DateInterface;
use Calcurates\ModuleMagento\Api\Data\DeliveryDate\DateInterfaceFactory;
use Calcurates\ModuleMagento\Api\Data\DeliveryDate\TimeIntervalInterface;
use Calcurates\ModuleMagento\Api\Data\DeliveryDate\TimeIntervalInterfaceFactory;
use Calcurates\ModuleMagento\Model\Carrier\DeliveryDateFormatter;
use Calcurates\ModuleMagento\Model\Config;
use Calcurates\ModuleMagento\Model\Config\Source\DeliveryDateDisplayTypeSource as DisplayType;
use Magento\Checkout\Model\Session;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Tax\Helper\Data;

class DeliveryDateProcessor
{
    private const ID_HASH_DELIMITER = '____';

    /**
     * @var DateInterfaceFactory
     */
    private $dateFactory;

    /**
     * @var TimeIntervalInterfaceFactory
     */
    private $timeIntervalFactory;

    /**
     * @var DeliveryDateFormatter
     */
    private $deliveryDateFormatter;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Data
     */
    private $taxHelper;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param DateInterfaceFactory $dateFactory
     * @param TimeIntervalInterfaceFactory $timeIntervalFactory
     * @param DeliveryDateFormatter $deliveryDateFormatter
     * @param EncryptorInterface $encryptor
     * @param Config $config
     * @param Data $taxHelper
     * @param Session $checkoutSession
     */
    public function __construct(
        DateInterfaceFactory $dateFactory,
        TimeIntervalInterfaceFactory $timeIntervalFactory,
        DeliveryDateFormatter $deliveryDateFormatter,
        EncryptorInterface $encryptor,
        Config $config,
        Data $taxHelper,
        Session $checkoutSession
    ) {
        $this->dateFactory = $dateFactory;
        $this->timeIntervalFactory = $timeIntervalFactory;
        $this->deliveryDateFormatter = $deliveryDateFormatter;
        $this->encryptor = $encryptor;
        $this->config = $config;
        $this->taxHelper = $taxHelper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param array $timeSlots
     * @return DateInterface[]
     */
    public function getDeliveryDates(array $timeSlots): array
    {
        $deliveryDates = [];

        foreach ($timeSlots as $timeSlot) {
            /** @var DateInterface $deliveryDate */
            $deliveryDate = $this->dateFactory->create();
            [$dateFrom] = $this->deliveryDateFormatter->prepareDates($timeSlot['date'], null);
            $dateFrom = $this->deliveryDateFormatter->formatSingleDate(
                $dateFrom,
                $this->config->getDeliveryDateDisplayType() === DisplayType::DAYS_QTY
                    ? DisplayType::DATES_MAGENTO_FORMAT
                    : $this->config->getDeliveryDateDisplayType()
            );
            $quote = $this->checkoutSession->getQuote();
            $deliveryDate->setDate($timeSlot['date']);
            $deliveryDate->setDateFormatted($dateFrom);
            $deliveryDate->setLabel($timeSlot['label'] ?? '');
            $deliveryDate->setFeeAmount((float)$timeSlot['extraFee']);
            $deliveryDate->setFeeAmountExclTax(
                $this->taxHelper->getShippingPrice(
                    (float)$timeSlot['extraFee'],
                    false,
                    $quote->getShippingAddress(),
                    $quote->getCustomerTaxClassId()
                )
            );
            $deliveryDate->setFeeAmountInclTax(
                $this->taxHelper->getShippingPrice(
                    (float)$timeSlot['extraFee'],
                    true,
                    $quote->getShippingAddress(),
                    $quote->getCustomerTaxClassId()
                )
            );
            $deliveryDate->setId($this->generateDateId($timeSlot));

            $timeIntervals = [];
            foreach ($timeSlot['time'] as $time) {
                $intervalFormatted = $this->deliveryDateFormatter->formatTimeInterval(
                    $time['from'],
                    $time['to']
                );

                /** @var TimeIntervalInterface $timeInterval */
                $timeInterval = $this->timeIntervalFactory->create();
                $timeInterval->setLabel($time['label'] ?? '');
                $timeInterval->setFeeAmount((float)$time['extraFee']);
                $timeInterval->setFeeAmountExclTax(
                    $this->taxHelper->getShippingPrice(
                        (float)$time['extraFee'],
                        false,
                        $quote->getShippingAddress(),
                        $quote->getCustomerTaxClassId()
                    )
                );
                $timeInterval->setFeeAmountInclTax(
                    $this->taxHelper->getShippingPrice(
                        (float)$time['extraFee'],
                        true,
                        $quote->getShippingAddress(),
                        $quote->getCustomerTaxClassId()
                    )
                );
                $timeInterval->setFrom($time['from']);
                $timeInterval->setTo($time['to']);
                $timeInterval->setId($this->generateTimeIntervalId($time));
                $timeInterval->setIntervalFormatted($intervalFormatted);

                $timeIntervals[] = $timeInterval;
            }

            $deliveryDate->setTimeIntervals($timeIntervals);

            $deliveryDates[] = $deliveryDate;
        }

        return $deliveryDates;
    }

    /**
     * @param array $date
     * @return string
     */
    private function generateDateId(array $date): string
    {
        $dateObject = new \DateTime($date['date']);
        $dateString = $dateObject->format('Y-m-d');

        return $this->encryptor->hash(
            $dateString . self::ID_HASH_DELIMITER . (float)$date['extraFee'],
            Encryptor::HASH_VERSION_MD5
        );
    }

    /**
     * @param array $timeInterval
     * @return string
     */
    private function generateTimeIntervalId(array $timeInterval): string
    {
        $string = implode(
            self::ID_HASH_DELIMITER,
            [
                $timeInterval['from'],
                $timeInterval['to'],
                (float)$timeInterval['extraFee'],
            ]
        );

        return $this->encryptor->hash($string, Encryptor::HASH_VERSION_MD5);
    }
}
