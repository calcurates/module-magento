<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\Client;

use Calcurates\ModuleMagento\Client\Http\ApiException;
use Magento\Framework\Exception\LocalizedException;

/**
 * @TODO bad implementation. No "client" use, refactor to independent command for each API method
 */
interface CalcuratesClientInterface
{
    const TYPE_CARRIERS = 'carriers';
    const TYPE_TABLE_RATES = 'table-rates';
    const TYPE_FLAT_RATES = 'flat-rates';
    const TYPE_FREE_SHIPPING = 'free-shipping';
    const TYPE_IN_STORE_PICKUP = 'in-store-pickups';
    const TYPE_RATE_SHOPPING = 'rate-shopping';

    /**
     * @param string $shippingCarrierId
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     */
    public function getShippingServices($shippingCarrierId, $storeId);

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     */
    public function getShippingCarriersWithServices($storeId);

    /**
     * @param string $url
     * @return string
     * @throws LocalizedException
     */
    public function getLabelContent($url);

    /**
     * @param string $serviceId
     * @param string $tracking
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     * @throws LocalizedException
     */
    public function getTrackingInfo($serviceId, $tracking, $storeId);

    /**
     * @param array $request
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     * @throws LocalizedException
     */
    public function createShippingLabel($request, $storeId);

    /**
     * @param $request
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     * @throws LocalizedException
     * @throws ApiException
     */
    public function getRates($request, $storeId);

    /**
     * @param array $request
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     * @throws LocalizedException
     * @throws ApiException
     */
    public function getRatesSimple(array $request, $storeId): array;

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     * @throws LocalizedException
     */
    public function getCustomPackages($storeId);

    /**
     * @param string $type
     * @param int|\Magento\Framework\App\ScopeInterface|string $storeId
     * @return array
     */
    public function getShippingOptions(string $type, $storeId): array;
}
