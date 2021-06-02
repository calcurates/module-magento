/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'jquery',
    'ko',
    'Magento_Ui/js/form/element/select',
    'mage/translate',
    'Calcurates_ModuleMagento/js/model/instore-pickup/stores-settings',
    'Calcurates_ModuleMagento/js/model/instore-pickup/method-parser',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/checkout-data',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/quote'
], function (
    $,
    ko,
    Select,
    $t,
    storesSettings,
    parseMethodDataFromCode,
    selectShippingMethodAction,
    checkoutData,
    priceUtils,
    quote
) {
    'use strict';

    return Select.extend({
        defaults: {
            caption: $t('Choose a store...'),
            template: 'Calcurates_ModuleMagento/in-store-pickup/pickup-store'
        },
        storeId: ko.observable(""),

        initObservable: function () {
            var selectedStoreId = storesSettings.getSelectedStoreId();

            this._super().observe('options');

            storesSettings.getStoresSettings().subscribe(function (data) {
                this.options(data);
            }, this);

            if (selectedStoreId) {
                this.storeId(selectedStoreId);
            }

            return this;
        },

        onChangeStore: function () {
            var storeId = this.storeId(),
                rate;
            storesSettings.getStoresSettings()().forEach(function (store) {
                if (store.storeId === storeId) {
                    rate = store.rate;
                }
            });

            if (rate) {
                selectShippingMethodAction(rate);
                checkoutData.setSelectedShippingRate(rate['carrier_code'] + '_' + rate['method_code']);
            } else {
                selectShippingMethodAction(null);
                checkoutData.setSelectedShippingRate(null);
            }
        },

        /**
         * Get Store Option Label.
         * @param {Object.<{storeId: String, storeTitle: String, rate: Object}>} store
         * @returns {String}
         */
        storeOptionsText: function (store) {
            var formattedPrice = priceUtils.formatPrice(store.rate.amount, quote.getPriceFormat());

            return store.storeTitle + ' - ' + formattedPrice;
        }
    });
});
