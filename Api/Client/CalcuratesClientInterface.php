<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\Client;

use Magento\Framework\Exception\LocalizedException;

interface CalcuratesClientInterface
{
    /**
     * @param string $shippingCarrierId
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     */
    public function getShippingServices($shippingCarrierId, $storeId);

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
     */
    public function getRates($request, $storeId);
}
