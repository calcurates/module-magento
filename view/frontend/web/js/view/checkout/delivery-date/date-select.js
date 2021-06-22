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
    'Calcurates_ModuleMagento/js/model/delivery-date/delivery-date-list',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/quote'
], function (
    $,
    ko,
    Select,
    $t,
    deliveryDateList,
    priceUtils,
    quote
) {
    'use strict';

    return Select.extend({
        defaults: {
            caption: $t('Choose a Delivery Date...'),
            template: 'Calcurates_ModuleMagento/delivery-date/date-select',
            label: $t('Delivery Date')
        },

        initObservable: function () {
            this._super().observe('options');

            deliveryDateList.currentDeliveryDatesList.subscribe(function (data) {
                var options = [];
                data.forEach(function (deliveryDate){
                    options.push({
                        value: deliveryDate.id,
                        label: this.dateText(deliveryDate)
                    })
                }.bind(this));
                this.setOptions(options);
                this.value("");
                this.onChangeDate();
            }, this);

            return this;
        },

        onChangeDate: function () {
            var currentDateId = this.value(),
                currentDate;

            deliveryDateList.currentDeliveryDatesList().forEach(function (date) {
                if (currentDateId === date.id) {
                    currentDate = date;
                }
            });

            deliveryDateList.currentDate(currentDate);
        },

        dateText: function (deliveryDate) {
            var optionLabel = deliveryDate.date_formatted,
                formattedPrice = '';

            if (deliveryDate.fee_amount) {
                formattedPrice = priceUtils.formatPrice(deliveryDate.fee_amount, quote.getPriceFormat());
                optionLabel += ' (+' + formattedPrice + ')';
            }

            return optionLabel;
        }
    });
});
