/**
 * @author Calcurates Team
 * @copyright Copyright © 2019 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define(['underscore'], function (_) {
    'use strict';

    return {
        /**
         * Format address to use in store pickup
         *
         * @param {Object} address
         * @return {*}
         */
        formatAddressToInStorePickupAddress: function (address) {
            if (address.getType() !== 'calcurates-in-store-pickup-address') {
                address = _.extend({}, address, {
                    saveInAddressBook: 0,

                    /**
                     * Is address can be used for billing
                     *
                     * @return {Boolean}
                     */
                    canUseForBilling: function () {
                        return false;
                    },

                    /**
                     * Returns address type
                     *
                     * @return {String}
                     */
                    getType: function () {
                        return 'calcurates-in-store-pickup-address';
                    },

                    /**
                     * Returns address key
                     *
                     * @return {*}
                     */
                    getKey: function () {
                        return this.getType();
                    }
                });
            }

            return address;
        }
    };
});
