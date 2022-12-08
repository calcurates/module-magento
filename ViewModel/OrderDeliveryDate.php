<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\ViewModel;

use Calcurates\ModuleMagento\Api\Data\OrderDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\OrderData\GetOrderDataInterface;
use Calcurates\ModuleMagento\Model\Carrier\DeliveryDateFormatter;
use Calcurates\ModuleMagento\Model\Config;
use Calcurates\ModuleMagento\Model\Config\Source\DeliveryDateDisplayTypeSource as DisplayType;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class OrderDeliveryDate implements ArgumentInterface
{
    /**
     * @var DeliveryDateFormatter
     */
    private $deliveryDateFormatter;

    /**
     * @var GetOrderDataInterface
     */
    private $getOrderData;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param DeliveryDateFormatter $deliveryDateFormatter
     * @param GetOrderDataInterface $getOrderData
     * @param Config $config
     */
    public function __construct(
        DeliveryDateFormatter $deliveryDateFormatter,
        GetOrderDataInterface $getOrderData,
        Config $config
    ) {
        $this->deliveryDateFormatter = $deliveryDateFormatter;
        $this->getOrderData = $getOrderData;
        $this->config = $config;
    }

    /**
     * @param array $deliveryDates
     * @return string
     */
    public function formatDeliveryDates(array $deliveryDates): string
    {
        return $this->deliveryDateFormatter->formatDeliveryDate(
            $deliveryDates['from'] ?? null,
            $deliveryDates['to'] ?? null,
            $this->config->getDeliveryDateDisplayType() === DisplayType::DAYS_QTY
                ? DisplayType::DATES_MAGENTO_FORMAT
                : $this->config->getDeliveryDateDisplayType()
        );
    }

    /**
     * @param int $orderId
     * @return OrderDataInterface|null
     */
    public function getOrderDataByOrderId(int $orderId): ?OrderDataInterface
    {
        return $this->getOrderData->get($orderId);
    }

    /**
     * @param string $date
     * @return string
     */
    public function formatDate(string $date): string
    {
        [$dateObject] = $this->deliveryDateFormatter->prepareDates($date, null);

        return $this->deliveryDateFormatter->formatSingleDate(
            $dateObject,
            $this->config->getDeliveryDateDisplayType() === DisplayType::DAYS_QTY
                ? DisplayType::DATES_MAGENTO_FORMAT
                : $this->config->getDeliveryDateDisplayType()
        );
    }

    /**
     * @param string $from
     * @param string $to
     * @return string
     * @throws \Exception
     */
    public function formatTimeInterval(string $from, string $to): string
    {
        return $this->deliveryDateFormatter->formatTimeInterval($from, $to);
    }
}
