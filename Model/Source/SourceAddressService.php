<?php

namespace Calcurates\ModuleMagento\Model\Source;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order\Shipment;

class SourceAddressService
{
    /**
     * @var ShipmentSourceCodeRetriever
     */
    private $shipmentSourceCodeRetriever;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * SourceAddressService constructor.
     * @param ShipmentSourceCodeRetriever $shipmentSourceCodeRetriever
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ShipmentSourceCodeRetriever $shipmentSourceCodeRetriever,
        ObjectManagerInterface $objectManager
    ) {
        $this->shipmentSourceCodeRetriever = $shipmentSourceCodeRetriever;
        $this->objectManager = $objectManager;
    }

    /**
     * @param Shipment $shipment
     * @return array|null
     */
    public function getAddressDataByShipment($shipment)
    {
        $sourceCode = $this->shipmentSourceCodeRetriever->retrieve($shipment);
        $addressData = null;
        if (SourceServiceContext::doesSourceExist() && !empty($sourceCode)) {
            $addressData = $this->getBySourceCode($sourceCode);
        }

        return $addressData;
    }

    /**
     * @param $sourceCode
     * @return array|null
     */
    private function getBySourceCode($sourceCode)
    {
        /** @var \Magento\InventoryApi\Api\SourceRepositoryInterface $sourceRepository */
        $sourceRepository = $this->objectManager->get(\Magento\InventoryApi\Api\SourceRepositoryInterface::class);
        try {
            $source = $sourceRepository->get($sourceCode);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return [
            'firstname' => $source->getContactName(),
            'lastname' => '',
            'company' => '',
            'street' => $source->getStreet(),
            'city' => $source->getCity(),
            'postcode' => $source->getPostcode(),
            'region' => $source->getRegion(),
            'country_id' => $source->getCountryId(),
            'email' => $source->getEmail(),
            'telephone' => $source->getPhone(),
        ];
    }
}
