<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\ShippingLabel\Model;

use Calcurates\ModuleMagento\Api\Data\TaxIdentifierInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\TaxIdentifier\CollectionFactory;
use Calcurates\ModuleMagento\Model\Shipment\CarriersSettingsProvider;
use Calcurates\ModuleMagento\Model\TaxIdentifierFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Shipping\Model\Shipping\LabelGenerator;

class LabelGeneratorPlugin
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CollectionFactory
     */
    private $taxIdsCollectionFactory;

    /**
     * @var TaxIdentifierFactory
     */
    private $taxIdFactory;

    /**
     * @param DataPersistorInterface $dataPersistor
     * @param SerializerInterface $serializer
     * @param CollectionFactory $taxIdsCollectionFactory
     * @param TaxIdentifierFactory $taxIdFactory
     */
    public function __construct(
        DataPersistorInterface $dataPersistor,
        SerializerInterface $serializer,
        CollectionFactory $taxIdsCollectionFactory,
        TaxIdentifierFactory $taxIdFactory
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->serializer = $serializer;
        $this->taxIdsCollectionFactory = $taxIdsCollectionFactory;
        $this->taxIdFactory = $taxIdFactory;
    }

    /**
     * Add additional request data to shipment before create label
     *
     * @param LabelGenerator $subject
     * @param Shipment $shipment
     * @param RequestInterface $request
     * @throws LocalizedException
     */
    public function beforeCreate(
        LabelGenerator $subject,
        Shipment $shipment,
        RequestInterface $request
    ) {
        $shippingServiceId = $request->getParam('calcuratesShippingServiceId');
        if (!$shippingServiceId) {
            throw new LocalizedException(__('Invalid Shipping Method'));
        }

        $shippingDate = $request->getParam('calcuratesShippingDate');
        if (!$shippingDate) {
            throw new LocalizedException(__('Invalid Shipping Date'));
        }

        if ($taxIdentifiers = $request->getParam('calcuratesTaxIds')) {
            $ids = $this->serializer->unserialize($taxIdentifiers);
            $this->saveTaxIdentifiers($ids);
            $shipment->setData(
                'calcuratesTaxIds',
                array_filter(
                    $ids,
                    function ($v) {
                        return $v['selected'] == true;
                    },
                    ARRAY_FILTER_USE_BOTH
                )
            );
        }

        $shipment->setData('calcuratesShippingServiceId', (int)$shippingServiceId);
        $shipment->setData('calcuratesShippingDate', $shippingDate);
    }

    /**
     * Remove Carriers Settings data from storage
     *
     * @param LabelGenerator $subject
     * @param $result
     * @param Shipment $shipment
     * @param RequestInterface $request
     */
    public function afterCreate(
        LabelGenerator $subject,
        $result,
        Shipment $shipment,
        RequestInterface $request
    ) {
        $this->dataPersistor->clear(CarriersSettingsProvider::CARRIERS_SETTINGS_DATA_CODE);
    }

    /**
     * @param array|null $identifiers
     * @return void
     */
    private function saveTaxIdentifiers($identifiers)
    {
        if (empty($identifiers)) {
            return;
        }
        $this->taxIdsCollectionFactory->create()->walk('delete');
        foreach ($identifiers as $identifier) {
            $this->taxIdFactory->create()
                ->setType($identifier[TaxIdentifierInterface::TYPE])
                ->setValue($identifier[TaxIdentifierInterface::VALUE])
                ->setIssueAuthority($identifier[TaxIdentifierInterface::ISSUING_AUTHORITY])
                ->setEntityType($identifier[TaxIdentifierInterface::ENTITY_TYPE])
                ->save();
        }
    }
}
