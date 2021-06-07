define([
    'ko',
    'Magento_Checkout/js/model/quote'
], function (ko, quote) {
    'use strict';

    return {
        deliveryDateList: ko.observable({}),
        currentDeliveryDatesList: ko.observable([]),
        currentDate: ko.observable({}),

        /**
         * Parse rates and update delivery dates list
         *
         * @param {Array.<{carrier_code: String, carrier_title: String, method_code: String, method_title: String, extension_attributes: Object}>} ratesData
         * @returns {void}
         */
        updateStoresSettingsFromRates: function (ratesData) {
            var deliveryDates = {},
                currentRate = quote.shippingMethod();

            ratesData.forEach(function (rate) {
                var calcuratesData = rate.extension_attributes.calcurates_data || {};
                if (rate.carrier_code !== 'calcurates') {
                    return;
                }

                if (!calcuratesData.delivery_dates_list) {
                    return;
                }

                deliveryDates[rate.carrier_code + '_' + rate.method_code] = calcuratesData.delivery_dates_list;

                if (currentRate && currentRate.carrier_code === rate.carrier_code && currentRate.method_code === rate.method_code) {
                    this.currentDeliveryDatesList(calcuratesData.delivery_dates_list)
                }
            }.bind(this));

            this.deliveryDateList(deliveryDates);
        },

        updateShippingMethod: function (method) {
            var currentDeliveryDates = [];

            if (method && method.carrier_code && method.method_code) {
                currentDeliveryDates = this.deliveryDateList()[method.carrier_code + '_' + method.method_code] || [];
            }

            this.currentDeliveryDatesList(currentDeliveryDates);
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