/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_ModuleMagento
 */

define([], function () {
    'use strict';

    return {
        /**
         * @return {Object}
         */
        getRules: function () {
            return {
                'firstname': {
                    'required': false
                },
                'lastname': {
                    'required': false
                },
                'company': {
                    'required': false
                },
                'telephone': {
                    'required': false
                },
                'postcode': {
                    'required': true
                },
                'country_id': {
                    'required': true
                },
                'region_id': {
                    'required': true
                },
                'region_id_input': {
                    'required': true
                }
            };
        }
    };
});
