<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Config\Source;

use Calcurates\ModuleMagento\Model\Carrier;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Config;

class ActiveShippingMethodsSource implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var Config
     */
    private $shippingConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ActiveShippingMethodsSource constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $shippingConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig, Config $shippingConfig)
    {
        $this->scopeConfig = $scopeConfig;
        $this->shippingConfig = $shippingConfig;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $carriers = $this->shippingConfig->getAllCarriers();

        $methods = [];
        foreach ($carriers as $carrierCode => $carrierModel) {
            if (!$carrierModel->isActive() || $carrierCode == Carrier::CODE) {
                continue;
            }
            $carrierTitle = $this->scopeConfig->getValue(
                'carriers/' . $carrierCode . '/title',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $methods[$carrierCode] = ['label' => $carrierTitle, 'value' => $carrierCode];
        }

        return $methods;
    }
}
