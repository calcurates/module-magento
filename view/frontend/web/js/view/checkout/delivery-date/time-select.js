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
            label: $t('Delivery Time Slot'),
            additionalCss: '',
            validationError: '',
            timeSlotTimeRequired: null,
        },

        initObservable: function () {
            this._super().observe(['options', 'timeSlotTimeRequired', 'additionalCss', 'validationError']);

            deliveryDateList.currentDate.subscribe(function (data) {
                var options = [];
                this.timeSlotTimeRequired(this.getTimeSlotTimeRequired(deliveryDateList));
                this.additionalCss(this.getAdditionalCss());

                if (data && data.time_intervals) {
                    data.time_intervals.forEach(function (timeInterval){
                        options.push({
                            value: timeInterval.id,
                            label: this.timeOptionsText(timeInterval)
                        })
                    }.bind(this));
                }
                if (options.length > 0) {
                    this.setOptions(options);
                    if (this.timeSlotTimeRequired()
                        && 'undefined' !== typeof options[0]
                    ) {
                        this.value(options[0].value);
                        this.validateSelect();
                    }
                    this.enable();
                } else {
                    this.options([]);
                    this.setOptions([]);
                    this.value();
                    this.disable();
                }
            }, this);

            return this;
        },

        onChangeTime: function () {
            if (this.options().length > 0) {
                this.validateSelect();
            }
        },

        timeOptionsText: function (timeInterval) {
            var optionLabel = timeInterval.interval_formatted,
                formattedPrice = '';

            if (timeInterval.fee_amount) {
                formattedPrice = priceUtils.formatPrice(timeInterval.fee_amount, quote.getPriceFormat());
                optionLabel += ' (+' + formattedPrice + ')';
            }

            return optionLabel;
        },

        getTimeSlotTimeRequired: function () {
            var deliveryDatesMetadata = deliveryDateList.getDeliveryDatesMetadata();

            return !!(deliveryDatesMetadata
                && deliveryDatesMetadata.time_slot_time_required === true);
        },

        getAdditionalCss: function () {
            var initialClasses = '';
            if (this.timeSlotTimeRequired()) {
                initialClasses += ' _required';
            }
            if (this.validationError()) {
                initialClasses += ' _error';
            }
            return initialClasses;
        },

        validateSelect: function () {
            if (this.getTimeSlotTimeRequired() === true
                && 'undefined' === typeof this.value()
            ) {
                this.validationError($t('This is a required field.'));
                this.additionalCss(this.getAdditionalCss());
                return false;
            }
            this.validationError('');
            this.additionalCss(this.getAdditionalCss());
            return true;
        }
    });
});
