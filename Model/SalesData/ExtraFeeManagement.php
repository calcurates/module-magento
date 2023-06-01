<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\SalesData;

use Calcurates\ModuleMagento\Api\SalesData\ExtraFeeManagementInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\StoreWebsiteRelationInterface;

/**
 * Class ExtraFeeManagement - manage Amasty extra fees
 */
class ExtraFeeManagement implements ExtraFeeManagementInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    /**
     * ExtraFeeManagement constructor.
     * @param ObjectManagerInterface $objectManager
     * @param ModuleManager $moduleManager
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ModuleManager $moduleManager,
        StoreWebsiteRelationInterface $storeWebsiteRelation
    ) {
        $this->storeWebsiteRelation = $storeWebsiteRelation;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
    }

    /**
     * @param int|null $websiteId
     * @return mixed[]
     */
    public function getFees($websiteId = null)
    {
        $result = [];
        if ($this->moduleManager->isEnabled('Amasty_Extrafee')) {
            $extraFeeCollection = $this->objectManager
                ->get(\Amasty\Extrafee\Model\ResourceModel\Fee\Collection::class);
            $extraFeeResourceModel = $this->objectManager
                ->get(\Amasty\Extrafee\Model\ResourceModel\Fee::class);
            $fees = $extraFeeCollection->getItems();
            $storeIds = array_merge($this->storeWebsiteRelation->getStoreByWebsiteId($websiteId), [0]);
            foreach ($fees as $fee) {
                $stores = $extraFeeResourceModel->lookupStoreIds($fee->getId());
                if (!empty(array_intersect($stores, $storeIds))) {
                    $extraFeeResourceModel->loadOptions($fee);
                    foreach ($fee->getOptions() as $option) {
                        $result[] = [
                            'id' => $option['entity_id'],
                            'group' => $fee->getName(),
                            'label' => $option['admin']
                        ];
                    }
                }
            }
        }
        return $result;
    }
}
