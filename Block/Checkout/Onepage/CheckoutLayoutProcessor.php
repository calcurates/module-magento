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
use Magento\Framework\Stdlib\ArrayManager;

class CheckoutLayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    public function __construct(
        Config $configProvider,
        ArrayManager $arrayManager
    ) {
        $this->configProvider = $configProvider;
        $this->arrayManager = $arrayManager;
    }

    /**
     * Remove store pickup selector if disabled
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        if (!$this->configProvider->isActive()) {
            return $jsLayout;
        }

        $path = 'components/checkout/children/steps/children/shipping-step/children';
        $jsLayout = $this->arrayManager->merge(
            $path,
            $jsLayout,
            [
                'shippingAddress' => [
                    'config' => [
                        'shippingMethodItemTemplate' =>
                            'Calcurates_ModuleMagento/shipping-address/shipping-method-item',
                        'shippingMethodListTemplate' =>
                            'Calcurates_ModuleMagento/shipping-address/shipping-method-list',
                    ],
                    'children' => [
                        'calcurates-store-selector' => [
                            'component' => 'Calcurates_ModuleMagento/js/view/checkout/instore-pickup/pickup-fieldset',
                            'displayArea' => 'before-shipping-method-form',
                            'sortOrder' => 20,
                            'dataScope' => 'calcurates-store-selector',
                            'provider' => 'checkoutProvider',
                            'config' => [
                                'deps' => [
                                    '0' => 'checkoutProvider',
                                ]
                            ],
                            'children' => [
                                'calcurates-store' => [
                                    'label'=> 'Select a store to collect your order:',
                                    'component' =>
                                        'Calcurates_ModuleMagento/js/view/checkout/instore-pickup/pickup-store',
                                    'dataScope' => 'calcurates-store',
                                    'provider' => 'checkoutProvider',
                                    'validation' => [
                                        'required-entry' => true
                                    ]
                                ]
                            ]
                        ],
                        'calcurates_delivery_date' => [
                            'component' =>
                                'Calcurates_ModuleMagento/js/view/checkout/delivery-date/delivery-date-fieldset',
                            'displayArea' => 'shippingAdditional',
                            'sortOrder' => 10,
                            'dataScope' => 'calcurates_delivery_date',
                            'provider' => 'checkoutProvider',
                            'config' => [
                                'deps' => [
                                    '0' => 'checkoutProvider',
                                ]
                            ],
                            'children' => [
                                'calcurates-delivery-date-date' => [
                                    'component' =>
                                        'Calcurates_ModuleMagento/js/view/checkout/delivery-date/date-select',
                                    'dataScope' => 'calcurates_delivery_date_id',
                                    'provider' => 'checkoutProvider',
                                    'validation' => [
                                        'required-entry' => true
                                    ]
                                ],
                                'calcurates-delivery-date-time' => [
                                    'component' =>
                                        'Calcurates_ModuleMagento/js/view/checkout/delivery-date/time-select',
                                    'dataScope' => 'calcurates_delivery_date_time_id',
                                    'provider' => 'checkoutProvider',
                                    'validation' => [
                                        'required-entry' => true
                                    ]
                                ]
                            ]
                        ],
                        'shipment_items' => [
                            'component' => 'Magento_Checkout/js/view/summary/item/details',
                            'displayArea' => 'shipment_items_details',
                            'children' => [
                                'thumbnail' => [
                                    'component' => 'Magento_Checkout/js/view/summary/item/details/thumbnail',
                                    'displayArea' => 'before_details',
                                ],
                                'subtotal' => [
                                    'component' => 'Magento_Checkout/js/view/summary/item/details/subtotal',
                                    'displayArea' => 'after_details',
                                ]
                            ]
                        ]
                    ]
                ],
                'step-config' => [
                    'children' => [
                        'shipping-rates-validation' => [
                            'children' => [
                                'calcurates-rates-validation' => [
                                    'component' =>
                                        'Calcurates_ModuleMagento/js/view/shipping-rates-validation/calcurates'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );

        $path = 'components/checkout/children/sidebar';
        $jsLayout = $this->arrayManager->merge(
            $path,
            $jsLayout,
            [
                'children' => [
                    'shipping-information' => [
                        'children' => [
                            'calcurates-delivery-date-sidebar' => [
                                'component' => 'Calcurates_ModuleMagento/js/view/checkout/delivery-date/sidebar',
                                'displayArea' => 'calcurates-delivery-date-sidebar',
                                'config' => [
                                    'template' => 'Calcurates_ModuleMagento/delivery-date/sidebar',
                                    'deps' => [
                                        '0' => 'checkout.sidebar.shipping-information'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );

        if ($this->configProvider->getStorePickupDisplayAs() !== DisplayInStorePickupAsSource::STORES_SELECTOR) {
            unset(
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                    ['shippingAddress']['children']['calcurates-store-selector']
            );
        }

        return $jsLayout;
    }
}
