/**
 * @author Calcurates Team
 * @copyright Copyright © 2019 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    '../../model/shipping-rates-validator/calcurates',
    '../../model/shipping-rates-validation-rules/calcurates'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    calcuratesShippingRatesValidator,
    calcuratesShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('calcurates', calcuratesShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('calcurates', calcuratesShippingRatesValidationRules);

    return Component;
});
