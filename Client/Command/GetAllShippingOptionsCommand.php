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
use Magento\Framework\Exception\LocalizedException;

class GetAllShippingOptionsCommand
{
    /**
     * @var GetShippingOptionsCommand
     */
    private $getShippingOptionsCommand;

    /**
     * GetAllShippingOptionsCommand constructor.
     * @param GetShippingOptionsCommand $getShippingOptionsCommand
     */
    public function __construct(GetShippingOptionsCommand $getShippingOptionsCommand)
    {
        $this->getShippingOptionsCommand = $getShippingOptionsCommand;
    }

    /**
     * @param int|\Magento\Framework\App\ScopeInterface|string $storeId
     * @return array
     */
    public function getShippingOptions($storeId)
    {
        try {
            $allShippingOptions = $this->getShippingOptionsCommand->get(
                (int) $storeId
            );
        } catch (LocalizedException $exception) {
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
}
