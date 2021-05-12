<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Block\Adminhtml\Shipping;

use Calcurates\ModuleMagento\Api\Data\ShippingLabelInterface;
use Calcurates\ModuleMagento\Api\ShippingLabelRepositoryInterface;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Shipment;

class ViewListLabels extends Template
{
    /**
     * @var ShippingLabelRepositoryInterface
     */
    private $shippingLabelRepository;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * ViewListLabels constructor.
     * @param Context $context
     * @param ShippingLabelRepositoryInterface $shippingLabelRepository
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        ShippingLabelRepositoryInterface $shippingLabelRepository,
        Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->shippingLabelRepository = $shippingLabelRepository;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Retrieve shipment model instance
     *
     * @return Shipment
     */
    public function getShipment()
    {
        return $this->coreRegistry->registry('current_shipment');
    }

    /**
     * @return \Calcurates\ModuleMagento\Model\ResourceModel\ShippingLabel\Collection
     */
    public function getShippingLabels()
    {
        $shipmentId = (int)$this->getShipment()->getId();
        $collection = $this->shippingLabelRepository->getListByShipmentId($shipmentId);
        $collection->addOrder(ShippingLabelInterface::CREATED_AT, 'ASC');

        return $collection;
    }
}
