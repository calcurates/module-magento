<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Command;

use Calcurates\ModuleMagento\Api\Client\CalcuratesClientInterface;
use Calcurates\ModuleMagento\Model\Carrier\ShippingMethodManager;

class GetAllShippingOptionsCommand
{
    /**
     * @var CalcuratesClientInterface
     */
    private $calcuratesClient;

    public function __construct(CalcuratesClientInterface $calcuratesClient)
    {

        $this->calcuratesClient = $calcuratesClient;
    }

    /**
     * @param int|\Magento\Framework\App\ScopeInterface|string $storeId
     * @return array
     */
    public function getShippingOptions($storeId)
    {
        $carriers = $this->calcuratesClient->getShippingOptions(CalcuratesClientInterface::TYPE_CARRIERS, $storeId);
        $tableRates = $this->calcuratesClient->getShippingOptions(CalcuratesClientInterface::TYPE_TABLE_RATES, $storeId);
        $freeShipping = $this->calcuratesClient->getShippingOptions(CalcuratesClientInterface::TYPE_FREE_SHIPPING,
            $storeId);
        $flatRates = $this->calcuratesClient->getShippingOptions(CalcuratesClientInterface::TYPE_FLAT_RATES, $storeId);

        $shippingOptions = [];
        foreach ($tableRates as $shippingOption) {
            foreach ($shippingOption['methods'] as $method) {
                $key = ShippingMethodManager::TABLE_RATE . '_' . $shippingOption['shippingOption']['id'] . '_' .
                    $method['id'];
                $title = $shippingOption['shippingOption']['name'] . ' - ' . $method['name'];
                $shippingOptions[$key] = $title;
            }
        }

        foreach ($flatRates as $shippingOption) {
            $key = ShippingMethodManager::FLAT_RATES . '_' . $shippingOption['shippingOption']['id'];
            $title = $shippingOption['shippingOption']['name'];
            $shippingOptions[$key] = $title;
        }

        foreach ($freeShipping as $shippingOption) {
            $key = ShippingMethodManager::FREE_SHIPPING . '_' . $shippingOption['shippingOption']['id'];
            $title = $shippingOption['shippingOption']['name'];
            $shippingOptions[$key] = $title;
        }

        foreach ($carriers as $shippingOption) {
            foreach ($shippingOption['services'] as $method) {
                $key = ShippingMethodManager::CARRIER . '_' . $shippingOption['shippingOption']['id'] .
                    '_' . $method['id'];
                $title = $shippingOption['shippingOption']['name'] . ' - ' . $method['name'];
                $shippingOptions[$key] = $title;
            }
        }

        return $shippingOptions;
    }
}
