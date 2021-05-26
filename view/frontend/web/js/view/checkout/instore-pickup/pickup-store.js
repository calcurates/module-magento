define([
    'jquery',
    'ko',
    'Magento_Ui/js/form/element/select',
    'mage/translate',
    'Calcurates_ModuleMagento/js/model/instore-pickup/stores-settings',
    'Calcurates_ModuleMagento/js/model/instore-pickup/method-parser',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/checkout-data'
], function (
    $,
    ko,
    Select,
    $t,
    storesSettings,
    parseMethodDataFromCode,
    selectShippingMethodAction,
    checkoutData
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
        }
    });
});
