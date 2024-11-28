<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Shipment\Manifest;

use Calcurates\ModuleMagento\Api\Data\ManifestInterfaceFactory;
use Calcurates\ModuleMagento\Api\ManifestSaveInterface;
use Calcurates\ModuleMagento\Api\ShippingLabelRepositoryInterface;
use Calcurates\ModuleMagento\Client\Command\CreateManifestsCommand;
use Calcurates\ModuleMagento\Client\Command\DownloadContentCommand;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Collection;
use Psr\Log\LoggerInterface;

class ManifestCreator
{
    private const KEY_SEPARATOR = '____';

    /**
     * @var CreateValidator
     */
    private $validator;

    /**
     * @var ShippingLabelRepositoryInterface
     */
    private $shippingLabelRepository;

    /**
     * @var ManifestInterfaceFactory
     */
    private $manifestFactory;

    /**
     * @var ManifestSaveInterface
     */
    private $manifestSave;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CreateManifestsCommand
     */
    private $createManifestsCommand;

    /**
     * @var DownloadContentCommand
     */
    private $downloadContentCommand;

    public function __construct(
        CreateValidator $validator,
        ShippingLabelRepositoryInterface $shippingLabelRepository,
        ManifestSaveInterface $manifestSave,
        ManifestInterfaceFactory $manifestFactory,
        LoggerInterface $logger,
        CreateManifestsCommand $createManifestsCommand,
        DownloadContentCommand $downloadContentCommand
    ) {
        $this->validator = $validator;
        $this->shippingLabelRepository = $shippingLabelRepository;
        $this->manifestFactory = $manifestFactory;
        $this->manifestSave = $manifestSave;
        $this->logger = $logger;
        $this->createManifestsCommand = $createManifestsCommand;
        $this->downloadContentCommand = $downloadContentCommand;
    }

    /**
     * @param Collection $shipmentsCollection
     * @throws CouldNotSaveException
     * @throws ValidatorException
     * @throws \Calcurates\ModuleMagento\Client\Http\ApiException
     */
    public function createManifestsForShipments(Collection $shipmentsCollection): void
    {
        $this->validator->validate($shipmentsCollection);
        $labels = $this->shippingLabelRepository->getListLastLabelsByShipments($shipmentsCollection->getAllIds());

        $labelExtIdHash = [];
        $labelIdsGrouped = [];
        foreach ($labels as $shipmentId => $label) {
            $labelData = $label->getLabelData();
            $labelId = $labelData['labelId'] ?? '';
            if (!$label->getManifestId()
                && !empty($labelId)
                && !isset($labelExtIdHash[$labelId])
            ) {
                /** @var Shipment $shipment */
                $shipment = $shipmentsCollection->getItemById($shipmentId);

                $labelExtIdHash[$labelId] = $label;
                $key = $label->getCarrierProviderCode()
                    . self::KEY_SEPARATOR
                    . $label->getCarrierCode()
                    . self::KEY_SEPARATOR
                    . $shipment->getStoreId();
                $labelIdsGrouped[$key][] = $labelId;
            }
        }

        foreach ($labelIdsGrouped as $key => $labelIds) {
            list($providerCode, $carrierCode, $storeId) = explode(self::KEY_SEPARATOR, $key);

            $storeId = (int)$storeId;

            $manifests = $this->createManifestsCommand->createManifests(
                $carrierCode,
                $providerCode,
                $labelIds,
                $storeId
            );

            foreach ($manifests as $manifestData) {
                $manifest = $this->manifestFactory->create();
                $manifest->setManifestData($manifestData);

                $manifestContent = $this->downloadContentCommand->download($manifestData['manifestDownload'], $storeId);
                $manifest->setPdfContent($manifestContent);

                try {
                    $this->manifestSave->save($manifest);
                } catch (CouldNotSaveException $e) {
                    $this->logger->critical($e);
                    throw $e;
                }

                foreach ($manifestData['labelsId'] as $labelId) {
                    $label = $labelExtIdHash[$labelId];
                    $label->setManifestId($manifest->getManifestId());
                    $this->shippingLabelRepository->save($label);
                }
            }
        }
    }
}
