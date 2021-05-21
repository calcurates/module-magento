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
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Collection;
use Magento\Framework\Validator\Exception as ValidatorException;

class CreateValidator
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
     * @throws ValidatorException
     */
    public function validate(Collection $shipmentsCollection): void
    {
        $shippingLabels = $this->shippingLabelRepository->getListLastLabelsByShipments(
            $shipmentsCollection->getAllIds()
        );
        $errors = [];
        /** @var ShippingLabel $label */
        foreach ($shippingLabels as $label) {
            if ($label->getManifestId()) {
                $errors['manifest_exists'][$label->getShipmentId()] = true;
            }
        }

        /** @var Shipment $shipment */
        foreach ($shipmentsCollection->getItems() as $shipment) {
            $shipmentId = $shipment->getId();
            if (!isset($shippingLabels[$shipmentId])) {
                $errors['label_does_not_exists'][$shipmentId] = $shipment->getIncrementId();
            }

            if (isset($errors['manifest_exists'][$shipmentId])) {
                $errors['manifest_exists'][$shipmentId] = $shipment->getIncrementId();
            }
        }

        $errorMessages = [];
        if (!empty($errors['label_does_not_exists'])) {
            $errorMessages[] = __(
                'Unable to create manifest: all of selected shipments must have shipping labels.'
                . ' The following shipments do not have shipping labels: %1',
                implode(',', $errors['label_does_not_exists'])
            );
        }

        if (!empty($errors['manifest_exists'])) {
            $errorMessages[] = __(
                'Unable to create manifest for selected shipments.'
                . ' The manifest is already created for the last shipping label of the following shipments: %1',
                implode(',', $errors['manifest_exists'])
            );
        }

        if ($errorMessages) {
            throw new ValidatorException(null, null, [$errorMessages]);
        }
    }
}
