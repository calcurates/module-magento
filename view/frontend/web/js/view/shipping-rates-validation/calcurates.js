/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_Integration
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
