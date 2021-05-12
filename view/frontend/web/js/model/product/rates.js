/**
 * @author Calcurates Team
 * @copyright Copyright © 2019 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'ko',
    'mage/storage',
    'Calcurates_ModuleMagento/js/action/estimation-loader'
], function (ko, storage, estimationLoaderAction) {
    'use strict';

    return {
        rates: ko.observableArray(),

        /**
         * @param {String} storeCode
         * @param {Array} productIds
         * @param {Boolean} isLoggedIn
         * @return {void}
         */
        loadLocations: function (storeCode, productIds, isLoggedIn) {
            var estimateUrl = 'rest/' + storeCode + '/V1/calcurates/estimate?',
                estimateGuestUrl = 'rest/' + storeCode + '/V1/calcurates/estimate-guest?',
                url;

            if (isLoggedIn) {
                url = estimateUrl;
            } else {
                url = estimateGuestUrl;
            }

            productIds.forEach(function (productId) {
                url += 'productIds[]=' + productId;
            });


            estimationLoaderAction.show();

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
