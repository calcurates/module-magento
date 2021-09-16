<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\UI\Component\Listing\Order\Shipment\Column\Carrier;

use Calcurates\ModuleMagento\Api\Data\ShippingLabelInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\ShippingLabel\Collection as LabelCollection;
use Calcurates\ModuleMagento\Model\ResourceModel\ShippingLabel\CollectionFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options - Ui carriers options
 */
class Options implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * Options constructor.
     * @param CollectionFactory $collectionFactory
     * @param Escaper $escaper
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Escaper $escaper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->escaper = $escaper;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $this->options = $this->prepareCarriersList();

        return $this->options;
    }

    /**
     * Prepare carriers list
     *
     * @return array
     */
    protected function prepareCarriersList(): array
    {
        $options = [];

        /** @var LabelCollection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect(
            [ShippingLabelInterface::SHIPPING_CARRIER_LABEL, ShippingLabelInterface::CARRIER_CODE]
        );
        $collection->getSelect()
            ->group(sprintf('main_table.%s', ShippingLabelInterface::CARRIER_CODE));

        if ($collection->count()) {
            /** @var ShippingLabelInterface $carrier */
            foreach ($collection->getItems() as $carrier) {
                $options[] = [
                    'label' => $this->escaper->escapeHtml($carrier->getShippingCarrierLabel() ?? ''),
                    'value' => $this->escaper->escapeHtml($carrier->getCarrierCode() ?? '')
                ];
            }
        }

        return $options;
    }
}
