/**
 * @author Calcurates Team
 * @copyright Copyright © 2019 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'underscore'
], function (quote, checkoutData, _) {
    'use strict';

    var storePickupShippingInformation = {
        defaults: {
            template: 'Calcurates_ModuleMagento/shipping-information'
        },

        /**
         * Get shipping method title based on delivery method.
         *
         * @return {String}
         */
        getShippingMethodTitle: function () {
            var shippingMethod = quote.shippingMethod(),
                title;

            if (!this.isStorePickup() && !this.isSplitCheckout()) {
                return this._super();
            }

            if (shippingMethod !== null && shippingMethod['carrier_code'] === 'calcurates') {
                title = shippingMethod['carrier_title'] + ' - ' + shippingMethod['method_title'];
            } else {
                title = this._super();
            }

            return title;
        },

        /**
         * Check if selected shipping method is splig checkout
         * @returns {false|*}
         */
        isSplitCheckout: function () {
            var method = quote.shippingMethod()

            return method
                && method.carrier_code === 'calcurates'
                && method.extension_attributes
                && method.extension_attributes.calcurates_metarate_data
        },

        /**
         * Get selected split shipment rates info
         * @returns {*[]}
         */
        getSplitShipments: function () {
            var shippingMethod = quote.shippingMethod(),
                selected = checkoutData.getSelectedSplitCheckoutShipments(),
                selectedView = []

            if (!shippingMethod || !this.isSplitCheckout()) {
                return []
            }

            _.each(selected, function (val, key) {
                let metaRate = shippingMethod.extension_attributes.calcurates_metarate_data.filter(function (item) {
                    return item.origin_id === parseInt(key)
                })
                let rate = _.first(metaRate).rates.filter(function (rate) {
                    return rate.carrier_code + '_' + rate.method_code === val
                })
                selectedView.push(_.first(rate))
            })

            return selectedView
        },

        /**
         * Get is store pickup delivery method selected.
         *
         * @returns {Boolean}
         */
        isStorePickup: function () {
            var shippingMethod = quote.shippingMethod(),
                isStorePickup = false;

            if (typeof this._super === 'function') {
                isStorePickup = this._super();
            }

            if (!isStorePickup && shippingMethod !== null && shippingMethod['method_code'] !== null) {
                var shippingMethodParts = shippingMethod['method_code'].split('_');
                isStorePickup = shippingMethod['carrier_code'] === 'calcurates' &&
                    shippingMethodParts[0] === 'inStorePickup';
            }

            return isStorePickup;
        }
    };

    return function (shippingInformation) {
        return shippingInformation.extend(storePickupShippingInformation);
    };
});
