/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/model/shipping-save-processor/payload-extender': {
                'Calcurates_ModuleMagento/js/model/shipping-save-processor/payload-extender-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Calcurates_ModuleMagento/js/view/shipping/shipping-mixin': true
            },
            'Magento_Checkout/js/model/quote': {
                'Calcurates_ModuleMagento/js/model/quote-mixin': true
            },
            'Magento_Checkout/js/view/shipping-information': {
                'Calcurates_ModuleMagento/js/view/shipping-information-mixin': true
            },
            'Magento_Checkout/js/checkout-data': {
                'Calcurates_ModuleMagento/js/checkout-data-mixin': true
            }
        }
    }
};
