var isEnabled = !!window.calcurates_module_enabled,
    isAmOscEnabled = !!window.am_osc_enabled
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

var config = {
    map: { "*": {} },
    config: {
        mixins: {
            "Magento_Checkout/js/model/shipping-save-processor/payload-extender": {
                "Calcurates_ModuleMagento/js/model/shipping-save-processor/payload-extender-mixin": isEnabled,
            },
            "Magento_Checkout/js/view/shipping": {
                "Calcurates_ModuleMagento/js/view/shipping/shipping-mixin": isEnabled,
            },
            "Magento_Checkout/js/model/quote": {
                "Calcurates_ModuleMagento/js/model/quote-mixin": isEnabled,
            },
            "Magento_Checkout/js/view/shipping-information": {
                "Calcurates_ModuleMagento/js/view/shipping-information-mixin": isEnabled,
            },
            "Magento_Checkout/js/checkout-data": {
                "Calcurates_ModuleMagento/js/checkout-data-mixin": isEnabled,
            },
            "Magento_Checkout/js/view/cart/shipping-rates": {
                "Calcurates_ModuleMagento/js/view/cart/shipping-rates-mixin": isEnabled,
            },
            "Magento_Checkout/js/model/shipping-rates-validator": {
                "Calcurates_ModuleMagento/js/model/shipping-rates-validator-mixin": isEnabled,
            },
            "Magento_Checkout/js/model/cart/totals-processor/default": {
                "Calcurates_ModuleMagento/js/model/cart/totals-processor/default-mixin": isEnabled,
            },
            "Magento_Paypal/js/order-review": {
                "Calcurates_ModuleMagento/js/order-review-mixin": isEnabled,
            },
            "Magento_Checkout/js/action/select-shipping-method": {
                "Calcurates_ModuleMagento/js/action/select-shipping-method-mixin": isEnabled,
            },
            "Magento_Swatches/js/swatch-renderer": {
                "Calcurates_ModuleMagento/js/swatch-renderer-mixin": isEnabled,
            },
            "Magento_ConfigurableProduct/js/configurable": {
                "Calcurates_ModuleMagento/js/configurable-mixin": isEnabled,
            },
            "Amasty_CheckoutCore/js/model/shipping-registry": {
                "Calcurates_ModuleMagento/js/am_osc/model/shipping-registry-mixin": isEnabled,
            },
            "Bss_OneStepCheckout/js/view/shipping": {
                "Calcurates_ModuleMagento/js/view/shipping/shipping-mixin": isEnabled,
            },
            "Bss_OneStepCheckout/js/model/shipping-save-processor/payload-extender": {
                "Calcurates_ModuleMagento/js/model/shipping-save-processor/payload-extender-mixin": isEnabled,
            },
            "Onestepcheckout_Iosc/js/shipping": {
                "Calcurates_ModuleMagento/js/iosc/shipping-mixin": isEnabled,
            },
        },
    },
}
if (isEnabled && isAmOscEnabled) {
    config.map["*"] = {
        "Amasty_CheckoutCore/template/onepage/shipping/methods.html":
            "Calcurates_ModuleMagento/template/am_osc/shipping/methods.html",
        "Magento_Checkout/js/action/get-totals": "Calcurates_ModuleMagento/js/am_osc/action/get-totals",
        "Amasty_CheckoutCore/js/action/get-totals": "Calcurates_ModuleMagento/js/am_osc/action/get-totals",
    }
}
