/**
 * @author Calcurates Team
 * @copyright Copyright © 2019 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
                },
                'city': {
                    'required': true
                },
                'street[0]': {
                    'required': false
                }
            };
        }
    };
});
