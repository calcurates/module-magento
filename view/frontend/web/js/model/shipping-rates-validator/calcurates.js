/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_Integration
 */

define([
    'jquery',
    'mageUtils',
    '../shipping-rates-validation-rules/calcurates',
    'mage/translate'
], function ($, utils, validationRules, $t) {
    'use strict';

    return {
        validationErrors: [],

        /**
         * @param {Object} address
         * @return {Boolean}
         */
        validate: function (address) {
            this.validationErrors = [];
            $.each(validationRules.getRules(), function (field, rule) {

                if (rule.required && utils.isEmpty(address[field])) {
                    var message = $t('Field ') + field + $t(' is required.'),
                        regionFields = ['region', 'region_id', 'region_id_input'];

                    if (
                        $.inArray(field, regionFields) === -1 ||
                        utils.isEmpty(address.region) && utils.isEmpty(address['region_id'])
                    ) {
                        this.validationErrors.push(message);
                    }
                }
            }).bind(this);

            return !this.validationErrors.length;
        }
    };
});
