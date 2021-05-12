<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

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
     * @var SourceServiceContext
     */
    private $sourceServiceContext;

    /**
     * SourceAddressService constructor.
     * @param ShipmentSourceCodeRetriever $shipmentSourceCodeRetriever
     * @param ObjectManagerInterface $objectManager
     * @param SourceServiceContext $sourceServiceContext
     */
    public function __construct(
        ShipmentSourceCodeRetriever $shipmentSourceCodeRetriever,
        ObjectManagerInterface $objectManager,
        SourceServiceContext $sourceServiceContext
    ) {
        $this->shipmentSourceCodeRetriever = $shipmentSourceCodeRetriever;
        $this->objectManager = $objectManager;
        $this->sourceServiceContext = $sourceServiceContext;
    }

    /**
     * @param Shipment $shipment
     * @return array|null
     */
    public function getAddressDataByShipment($shipment)
    {
        $sourceCode = $this->shipmentSourceCodeRetriever->retrieve($shipment);
        $addressData = null;
        if ($this->sourceServiceContext->isInventoryEnabled() && !empty($sourceCode)) {
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
