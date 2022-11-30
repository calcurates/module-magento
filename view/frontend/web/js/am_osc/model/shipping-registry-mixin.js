/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'mage/utils/wrapper'
], function (wrapper) {
    return function (AmOscShippingRegistry) {
        let original =  AmOscShippingRegistry.isHaveUnsavedShipping;

        return wrapper.extend(AmOscShippingRegistry, {
            isHaveUnsavedShipping: function () {
                let result = original.apply(this)
                if (!result && this.shippingMethod === 'metarate' && this.shippingCarrier === 'calcurates') {
                    return true
                }

                return result
            }
        })
    }
})
