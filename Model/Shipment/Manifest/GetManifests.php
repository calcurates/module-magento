<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Shipment\Manifest;

use Calcurates\ModuleMagento\Api\Data\ManifestInterface;
use Calcurates\ModuleMagento\Api\ShippingLabelRepositoryInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\Manifest\CollectionFactory;

class GetManifests
{
    /**
     * @var ShippingLabelRepositoryInterface
     */
    private $shippingLabelRepository;

    /**
     * @var CollectionFactory
     */
    private $manifestCollectionFactory;

    public function __construct(
        ShippingLabelRepositoryInterface $shippingLabelRepository,
        CollectionFactory $manifestCollectionFactory
    ) {
        $this->shippingLabelRepository = $shippingLabelRepository;
        $this->manifestCollectionFactory = $manifestCollectionFactory;
    }

    /**
     * @param array $shipmentIds
     * @return ManifestInterface[]
     */
    public function getManifestsByShipmentIds(array $shipmentIds): array
    {
        $labels = $this->shippingLabelRepository->getListLastLabelsByShipments($shipmentIds);

        $manifestIds = [];
        foreach ($labels as $label) {
            $manifestId = $label->getManifestId();
            $manifestIds[$manifestId] = $manifestId;
        }

        $manifestCollection = $this->manifestCollectionFactory->create();
        $manifestCollection->addFieldToFilter(ManifestInterface::MANIFEST_ID, ['in' => $manifestIds]);

        return $manifestCollection->getItems();
    }
}
