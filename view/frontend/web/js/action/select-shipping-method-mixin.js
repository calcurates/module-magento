/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'mage/utils/wrapper'
], function (wrapper) {
    return function (selectShippingMethod) {
        return wrapper.wrap(selectShippingMethod, function (originalSelectShippingMethod, method) {
            if (method && method.carrier_code === 'calcurates' && method.method_code === null) {
                method = null
            }
            originalSelectShippingMethod(method);
        });
    }
})
