
define([
    './stores-settings-registry',
    './method-parser',
    'Magento_Checkout/js/checkout-data'
], function (storesSettingsRegistry, parseMethodDataFromCode, checkoutData) {
    'use strict';

    return {

        /**
         * Get selected pickup store from selected shipping method. Empty string if it doesn't exists
         * @returns {String}
         */
        getSelectedStoreId: function () {
            var selectedShippingRate = checkoutData.getSelectedShippingRate();

            if (selectedShippingRate) {
                var methodData = parseMethodDataFromCode(selectedShippingRate.replace('calcurates_', ''));

                if (methodData.type === 'inStorePickup') {
                    return methodData.sub_id;
                }
            }

            return '';
        },

        /**
         * Parse rates and update stores settings
         *
         * @param {Array.<{carrier_code: String, carrier_title: String, method_code: String, method_title: String, extension_attributes: Object}>} ratesData
         * @returns {void}
         */
        updateStoresSettingsFromRates: function (ratesData) {
            var storesData = [];

            ratesData.forEach(function (rate) {
                var methodData;
                if (rate.carrier_code !== 'calcurates') {
                    return;
                }

                methodData = parseMethodDataFromCode(rate.method_code);

                if (methodData.type !== 'inStorePickup') {
                    return;
                }

                storesData.push({
                    rate: rate,
                    storeId: methodData.sub_id,
                    storeTitle: rate.method_title
                });
            });

            storesSettingsRegistry.setStoresSettings(storesData);
        },

        /**
         * Get stores settings
         *
         * @returns {*}
         */
        getStoresSettings: function () {
            return storesSettingsRegistry.getStoresSettings();
        }
    };
});
