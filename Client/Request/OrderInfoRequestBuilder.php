<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2023 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */
declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Request;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

class OrderInfoRequestBuilder
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @param OrderInterface $order
     * @return array
     */
    public function build(OrderInterface $order): array
    {
        if ($this->dataPersistor->get('last_ulid')) {
            $apiRequestBody = [
                'ulid' => $this->dataPersistor->get('last_ulid'),
                'order' => [
                    'id' => $order->getEntityId(),
                    'date' => $order->getCreatedAt()
                ]
            ];
            $this->dataPersistor->clear('last_ulid');
            return $apiRequestBody;
        }
        return [];
    }
}
