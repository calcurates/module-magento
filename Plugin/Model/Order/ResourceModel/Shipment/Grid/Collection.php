<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Model\Order\ResourceModel\Shipment\Grid;

use Magento\Sales\Model\ResourceModel\Order\Shipment\Grid\Collection as ShipmentCollection;
use Psr\Log\LoggerInterface;
use Zend_Db_Select_Exception;

/**
 * Class Collection - Shipping Grid Collection Plugin
 */
class Collection
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Collection constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ShipmentCollection $collection
     * @param mixed ...$args
     * @return array
     */
    public function beforeAddFieldToFilter(
        ShipmentCollection $collection,
        ...$args
    ) {
        $collection->addFilterToMap('created_at', 'main_table.created_at');
        return $args;
    }

    /**
     * @param ShipmentCollection $collection
     */
    public function beforeGetItems(
        ShipmentCollection $collection
    ) {
        try {
            $collection->getSelect()
                ->joinLeft(
                    ['calcurates_shipping_label' => $collection->getTable('calcurates_shipping_label')],
                    'main_table.entity_id = calcurates_shipping_label.shipment_id',
                    ['carrier_code']
                )
                ->group('main_table.entity_id');
        } catch (Zend_Db_Select_Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
