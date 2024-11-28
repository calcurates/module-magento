<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Command;

use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class GetAllShippingOptionsCommand
{
    /**
     * @var GetShippingOptionsCommand
     */
    private $getShippingOptionsCommand;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * GetAllShippingOptionsCommand constructor.
     * @param GetShippingOptionsCommand $getShippingOptionsCommand
     * @param StoreManagerInterface $storeManager
     * @param DataPersistorInterface|null $dataPersistor
     */
    public function __construct(
        GetShippingOptionsCommand $getShippingOptionsCommand,
        StoreManagerInterface $storeManager,
        DataPersistorInterface $dataPersistor = null
    ) {
        $this->storeManager = $storeManager;
        $this->getShippingOptionsCommand = $getShippingOptionsCommand;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @param int|\Magento\Framework\App\ScopeInterface|string $storeId
     * @return array
     */
    public function getShippingOptions($storeId)
    {
        if ($this->dataPersistor && $this->dataPersistor->get('all_methods_shipping_rule') && !$storeId) {
            $allShippingOptions = [];
            $storeIds = array_keys($this->storeManager->getStores());
            foreach ($storeIds as $storeId) {
                $allShippingOptions = array_merge_recursive(
                    $allShippingOptions,
                    $this->getAllShippingOptions($storeId)
                );
            }
        } else {
            $allShippingOptions = $this->getAllShippingOptions($storeId);
        }

        if (!$allShippingOptions) {
            return [];
        }
        $shippingOptions = [];

        foreach ($allShippingOptions['tableRates'] as $shippingOption) {
            foreach ($shippingOption['methods'] as $method) {
                $key = ShippingMethodManager::TABLE_RATE . '_' . $shippingOption['shippingOption']['id'] . '_' .
                    $method['id'];
                $title = $shippingOption['shippingOption']['name'] . ' - ' . $method['name'];
                $shippingOptions[$key] = $title;
            }
        }

        foreach ($allShippingOptions['inStorePickups'] as $shippingOption) {
            foreach ($shippingOption['stores'] as $store) {
                $key = ShippingMethodManager::IN_STORE_PICKUP . '_' . $shippingOption['shippingOption']['id'] . '_' .
                    $store['id'];
                $title = $shippingOption['shippingOption']['name'] . ' - ' . $store['name'];
                $shippingOptions[$key] = $title;
            }
        }

        foreach ($allShippingOptions['flatRates'] as $shippingOption) {
            $key = ShippingMethodManager::FLAT_RATES . '_' . $shippingOption['shippingOption']['id'];
            $title = $shippingOption['shippingOption']['name'];
            $shippingOptions[$key] = $title;
        }

        foreach ($allShippingOptions['freeShipping'] as $shippingOption) {
            $key = ShippingMethodManager::FREE_SHIPPING . '_' . $shippingOption['shippingOption']['id'];
            $title = $shippingOption['shippingOption']['name'];
            $shippingOptions[$key] = $title;
        }

        foreach ($allShippingOptions['carriers'] as $shippingOption) {
            foreach ($shippingOption['services'] as $method) {
                $key = ShippingMethodManager::CARRIER . '_' . $shippingOption['shippingOption']['id'] .
                    '_' . $method['id'];
                $title = $shippingOption['shippingOption']['name'] . ' - ' . $method['name'];
                $shippingOptions[$key] = $title;
            }
        }

        foreach ($allShippingOptions['rateShopping'] as $shippingOption) {
            foreach ($shippingOption['carriers'] as $carrier) {
                foreach ($carrier['services'] as $method) {
                    $key = ShippingMethodManager::RATE_SHOPPING . '_' . $shippingOption['shippingOption']['id'] .
                        '_' . $carrier['id'] . '_' . $method['id'];
                    $title = $shippingOption['shippingOption']['name'] . ' - ' . $carrier['carrierName']
                        . ' - ' . $method['name'];
                    $shippingOptions[$key] = $title;
                }
            }
        }

        return $shippingOptions;
    }

    /**
     * @param $storeId
     * @return array
     */
    private function getAllShippingOptions($storeId): array
    {
        try {
            return $this->getShippingOptionsCommand->get(
                (int) $storeId
            );
        } catch (LocalizedException $exception) {
            return [];
        }
    }
}
