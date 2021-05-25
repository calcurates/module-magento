<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Shipment\Manifest;

use Calcurates\ModuleMagento\Api\ShippingLabelRepositoryInterface;
use Calcurates\ModuleMagento\Model\ShippingLabel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Collection;

class PrintValidator
{
    /**
     * @var ShippingLabelRepositoryInterface
     */
    private $shippingLabelRepository;

    public function __construct(ShippingLabelRepositoryInterface $shippingLabelRepository)
    {
        $this->shippingLabelRepository = $shippingLabelRepository;
    }

    /**
     * @param Collection $shipmentsCollection
     * @throws LocalizedException
     */
    public function validate(Collection $shipmentsCollection): void
    {
        $shippingLabels = $this->shippingLabelRepository->getListLastLabelsByShipments(
            $shipmentsCollection->getAllIds()
        );

        $incorrectShipments = [];
        /** @var ShippingLabel $label */
        foreach ($shippingLabels as $label) {
            if (!$label->getManifestId()) {
                $incorrectShipments[$label->getShipmentId()] = true;
            }
        }

        /** @var Shipment $shipment */
        foreach ($shipmentsCollection->getItems() as $shipment) {
            $shipmentId = $shipment->getId();
            if (!isset($shippingLabels[$shipmentId])) {
                $incorrectShipments[$shipmentId] = $shipment->getIncrementId();
            }

            if (isset($incorrectShipments[$shipmentId])) {
                $incorrectShipments[$shipmentId] = $shipment->getIncrementId();
            }
        }

        if ($incorrectShipments) {
            throw new LocalizedException(__(
                'Unable to print manifests for selected shipments.'
                . ' The following shipments are missing created manifest for the last shipping label: %1',
                implode(',', $incorrectShipments)
            ));
        }
    }
}
