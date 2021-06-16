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
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;

class DeliveryDateProcessor
{
    private const ID_HASH_DELIMITTER = '____';

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

    public function __construct(
        DateInterfaceFactory $dateFactory,
        TimeIntervalInterfaceFactory $timeIntervalFactory,
        DeliveryDateFormatter $deliveryDateFormatter,
        EncryptorInterface $encryptor
    ) {
        $this->dateFactory = $dateFactory;
        $this->timeIntervalFactory = $timeIntervalFactory;
        $this->deliveryDateFormatter = $deliveryDateFormatter;
        $this->encryptor = $encryptor;
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
            $dateFrom = $this->deliveryDateFormatter->formatSingleDate($dateFrom);
            $deliveryDate->setDate($timeSlot['date']);
            $deliveryDate->setDateFormatted($dateFrom);
            $deliveryDate->setFeeAmount((float)$timeSlot['extraFee']);
            $deliveryDate->setId($this->generateDateId($timeSlot));

            $timeIntervals = [];
            foreach ($timeSlot['time'] as $time) {
                /** @var TimeIntervalInterface $timeInterval */
                $timeInterval = $this->timeIntervalFactory->create();
                $timeInterval->setFeeAmount((float)$time['extraFee']);
                $timeInterval->setFrom($time['from']);
                $timeInterval->setTo($time['to']);
                $timeInterval->setId($this->generateTimeIntervalId($time));

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
            $dateString . self::ID_HASH_DELIMITTER . (float)$date['extraFee'],
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
            self::ID_HASH_DELIMITTER,
            [
                $timeInterval['from'],
                $timeInterval['to'],
                (float)$timeInterval['extraFee'],
            ]
        );

        return $this->encryptor->hash($string, Encryptor::HASH_VERSION_MD5);
    }
}
