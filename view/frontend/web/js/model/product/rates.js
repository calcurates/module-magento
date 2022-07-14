/**
 * @author Calcurates Team
 * @copyright Copyright © 2019 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'ko',
    'mage/storage',
    'underscore',
    'Calcurates_ModuleMagento/js/action/estimation-loader'
], function (ko, storage, _, estimationLoaderAction) {
    'use strict';

    return {
        rates: ko.observableArray(),

        /**
         * @param {String} storeCode
         * @param {Array} productIds
         * @param {Boolean} isLoggedIn
         * @param {Object} shipTo
         * @return {void}
         */
        loadLocations: function (storeCode, productIds, isLoggedIn, shipTo = null, showLoader = true) {
            var estimateUrl = 'rest/' + storeCode + '/V1/calcurates/estimate?',
                estimateGuestUrl = 'rest/' + storeCode + '/V1/calcurates/estimate-guest?',
                url;

            if (isLoggedIn) {
                url = estimateUrl;
            } else {
                url = estimateGuestUrl;
            }

            url += productIds.map(function (productId) {
                return 'productIds[]=' + productId;
            }).join('&');

            if (_.isObject(shipTo) && !_.isEmpty(shipTo)) {
                url += '&' + Object.keys(shipTo).map(function(key) {
                    return 'shipTo[' + key + ']' + '=' + shipTo[key];
                }).join('&');
            }

            if (showLoader) {
                estimationLoaderAction.show();
            }

            storage
                .get(url)
                .success(function (response) {
                    if (!response) {
                        return;
                    }

                    this.setRates(response);
                }.bind(this))
                .fail(function (response) {
                    console.log(response);
                })
                .always(function () {
                    estimationLoaderAction.hide();
                });


        },

        /**
         * @param {Array} rates
         * @return {void}
         */
        setRates: function (rates) {
            this.rates(rates);
        }
    }

});
