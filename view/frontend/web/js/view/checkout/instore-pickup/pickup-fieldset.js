/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'jquery',
    'mage/translate',
    'ko',
    'uiCollection',
    'Calcurates_ModuleMagento/js/model/instore-pickup/stores-settings',
    'Magento_Checkout/js/model/shipping-service',
    'Calcurates_ModuleMagento/js/action/wait-for-element'
], function ($, $t, ko, Component, storesSettings, shippingService, waitForElementAction) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Calcurates_ModuleMagento/in-store-pickup/pickup-fieldset',
            shippingMethodsSelector: '#checkout-shipping-method-load',
            shippingButtonLabel: $t('Shipping'),
            pickupButtonLabel: $t('In-Store Pickup')
        },

        visible: ko.observable(false),
        isStorePickup: ko.observable(false),

        initialize: function () {
            this._super();
            return this;
        },

        initObservable: function () {
            this._super();
            shippingService.getShippingRates().subscribe(this.onChangeRates.bind(this));

            storesSettings.getStoresSettings().subscribe(this.onChangeStores.bind(this));

            this.isStorePickup.subscribe(this.onChangeStorePickup.bind(this));

            if (storesSettings.getSelectedStoreId()) {
                this.isStorePickup(true);
            }

            return this;
        },

        choseShipping: function () {
            this.isStorePickup(false);
        },

        chosePickup: function () {
            this.isStorePickup(true);
        },

        onChangeRates: function (ratesData) {
            storesSettings.updateStoresSettingsFromRates(ratesData);
            this.hideStorePickupMethods();
        },

        onChangeStores: function (stores) {
            this.visible(stores.length > 0);
        },

        onChangeStorePickup: function (isStorePickup){
            waitForElementAction(this.shippingMethodsSelector, function (element) {
                element.toggle(!isStorePickup);
            });
        },

        hideStorePickupMethods: function () {
            storesSettings.getStoresSettings()().forEach(function (item) {
                var value = item.rate['carrier_code'] + '_' + item.rate['method_code'];

                waitForElementAction(this.shippingMethodsSelector + ' input[value="' + value + '"]', function (element) {
                    element.closest('tr').hide();
                })
            }.bind(this))
        }
    });
});
