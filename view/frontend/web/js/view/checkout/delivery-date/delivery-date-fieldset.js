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
    'Magento_Checkout/js/model/shipping-service',
    'Calcurates_ModuleMagento/js/model/delivery-date/delivery-date-list',
    'Magento_Checkout/js/model/quote'
], function ($, $t, ko, Component, shippingService, deliveryDateList, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Calcurates_ModuleMagento/delivery-date/delivery-date-fieldset'
        },

        visible: ko.observable(false),

        initialize: function () {
            this._super();
            return this;
        },

        initObservable: function () {
            this._super();
            shippingService.getShippingRates().subscribe(
                deliveryDateList.updateStoresSettingsFromRates,
                deliveryDateList
            );
            deliveryDateList.updateStoresSettingsFromRates(shippingService.getShippingRates()());
            quote.shippingMethod.subscribe(deliveryDateList.updateShippingMethod, deliveryDateList);

            deliveryDateList.currentDeliveryDatesList.subscribe(this.onDeliveryDatesChanged, this);
            this.onDeliveryDatesChanged(deliveryDateList.currentDeliveryDatesList());

            return this;
        },

        onDeliveryDatesChanged: function (deliveryDates){
            this.visible(deliveryDates.length > 0);
        }
    });
});
