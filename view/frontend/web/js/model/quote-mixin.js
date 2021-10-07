/**
 * @author Calcurates Team
 * @copyright Copyright © 2019 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'ko',
    'Calcurates_ModuleMagento/js/model/pickup-address-converter'
], function (ko, pickupAddressConverter) {
    'use strict';

    return function (quote) {
        var shippingAddress = quote.shippingAddress;

        /**
         * Makes sure that shipping address gets appropriate type when it points
         * to a store pickup location.
         */
        quote.shippingAddress = ko.pureComputed({
            /**
             * Return quote shipping address
             */
            read: function () {
                return shippingAddress();
            },

            /**
             * Set quote shipping address
             */
            write: function (address) {
                var shippingMethod = quote.shippingMethod();
                if (shippingMethod !== null) {
                    var shippingMethodParts = shippingMethod['method_code'].split('_');
                    if (shippingMethod['carrier_code'] === 'calcurates'
                        && shippingMethodParts[0] === 'inStorePickup'
                    ) {
                        shippingAddress(pickupAddressConverter.formatAddressToPickupAddress(address));
                    }
                } else {
                    shippingAddress(address);
                }
            }
        });

        return quote;
    };
});
