<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Block\Checkout\Onepage;

use Calcurates\ModuleMagento\Model\Config;
use Calcurates\ModuleMagento\Model\Config\Source\DisplayInStorePickupAsSource;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

class CheckoutLayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var Config
     */
    private $configProvider;

    public function __construct(Config $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * Remove store pickup selector if disabled
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        if ($this->configProvider->getStorePickupDisplayAs() !== DisplayInStorePickupAsSource::STORES_SELECTOR) {
            unset(
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                    ['shippingAddress']['children']['calcurates-store-selector']
            );
        }

        return $jsLayout;
    }
}
