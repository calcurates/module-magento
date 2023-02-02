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
    public const TYPE_CARRIERS = 'carriers';
    public const TYPE_TABLE_RATES = 'table-rates';
    public const TYPE_FLAT_RATES = 'flat-rates';
    public const TYPE_FREE_SHIPPING = 'free-shipping';
    public const TYPE_IN_STORE_PICKUP = 'in-store-pickups';
    public const TYPE_RATE_SHOPPING = 'rate-shopping';

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     * @deprecated since 1.28.0
     * @see \Calcurates\ModuleMagento\Gateway\CarriersServicesOptionSource
     */
    public function getShippingCarriersWithServices($storeId);

    /**
     * @param string $url
     * @param int $storeId
     * @return string
     * @throws LocalizedException
     */
    public function getLabelContent(string $url, int $storeId): string;

    /**
     * @param string $serviceId
     * @param string $tracking
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     * @throws LocalizedException
     */
    public function getTrackingInfo(string $serviceId, string $tracking, $storeId): array;

    /**
     * @param array $request
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     * @throws LocalizedException
     */
    public function createShippingLabel(array $request, $storeId): array;

    /**
     * @param array $request
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     * @throws LocalizedException
     * @throws ApiException
     */
    public function getRates(array $request, $storeId): array;

    /**
     * @param array $request
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     * @throws LocalizedException
     * @throws ApiException
     */
    public function getRatesSimple(array $request, $storeId): array;

    /**
     * @param array $request
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     * @throws LocalizedException
     * @throws ApiException
     */
    public function getRatesSplitCheckout(array $request, $storeId): array;

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
     * @deprecated since 1.28.0
     * @see \Calcurates\ModuleMagento\Client\Command\GetShippingOptionsCommand
     */
    public function getShippingOptions(string $type, $storeId): array;

    /**
     * @param array $request
     * @param int $storeId
     * @return array
     */
    public function populateOrderInfo(array $request, $storeId): array;
}
