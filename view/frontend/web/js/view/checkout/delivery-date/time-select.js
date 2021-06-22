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
            caption: $t('Choose a Delivery Time...'),
            template: 'Calcurates_ModuleMagento/delivery-date/time-select',
            label: $t('Delivery Time Slot')
        },

        initObservable: function () {
            this._super().observe('options');

            deliveryDateList.currentDate.subscribe(function (data) {
                var options = [];
                if (data && data.time_intervals) {
                    data.time_intervals.forEach(function (timeInterval){
                        options.push({
                            value: timeInterval.id,
                            label: this.timeOptionsText(timeInterval)
                        })
                    }.bind(this));
                }
                this.setOptions(options);
                this.value("");
            }, this);

            return this;
        },

        onChangeTime: function () {
        },

        timeOptionsText: function (timeInterval) {
            var optionLabel = timeInterval.interval_formatted,
                formattedPrice = '';

            if (timeInterval.fee_amount) {
                formattedPrice = priceUtils.formatPrice(timeInterval.fee_amount, quote.getPriceFormat());
                optionLabel += ' (+' + formattedPrice + ')';
            }

            return optionLabel;
        }
    });
});
