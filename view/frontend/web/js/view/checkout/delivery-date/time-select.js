/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'jquery',
    'ko',
    'Calcurates_ModuleMagento/js/component/form/element/select',
    'mage/translate',
    'Calcurates_ModuleMagento/js/model/delivery-date/delivery-date-list',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/set-shipping-information'
], function (
    $,
    ko,
    Select,
    $t,
    deliveryDateList,
    priceUtils,
    quote,
    setShippingInformationAction
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

            deliveryDateList.currentDate.subscribe(this.onCurrentDateChange, this);
            this.onCurrentDateChange(deliveryDateList.currentDate());
            return this;
        },

        onCurrentDateChange: function (data) {
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
                if (this.timeSlotTimeRequired()) {
                    this.caption(null);
                }
                this.updateCurrentTimeSlot(data.time_intervals);
                this.setOptions(options);
                this.setDefaultOption(data.time_intervals);
                this.updateValue();
                this.enable();
                this.show();
            } else {
                this.options([]);
                this.setOptions([]);
                this.value();
                this.disable();
                this.hide();
            }
        },

        updateValue: function () {
            if (deliveryDateList.currentTimeSlot() && deliveryDateList.currentTimeSlot().id) {
                this.value(deliveryDateList.currentTimeSlot().id);
                this.value.valueHasMutated();
                return;
            }
            if (this.timeSlotTimeRequired() && this.options().length) {
                this.value(this.default);
                this.validateSelect();
            }
        },

        updateCurrentTimeSlot: function (data) {
            var currTimeSlot = deliveryDateList.currentTimeSlot, newTimeSlot;

            if (data.length && currTimeSlot() && currTimeSlot().id) {
                data.forEach(function (timeSlot) {
                    if (timeSlot.interval_formatted === currTimeSlot().interval_formatted) {
                        newTimeSlot = timeSlot;
                    }
                })
                if (newTimeSlot && currTimeSlot().id !== newTimeSlot.id) {
                    currTimeSlot(newTimeSlot);
                } else if (!newTimeSlot) {
                    currTimeSlot({});
                }
            }
        },

        setDefaultOption: function (data) {
            var matchedOption
            if (!data || !data.length) {
                return;
            }
            if (this.defaultValueType === 'earliest_cheapest') {
                matchedOption = data.reduce(function (prev, curr) {
                    return (prev.fee_amount > curr.fee_amount) ? curr : prev;
                })
                matchedOption = matchedOption.id;
            } else {
                matchedOption = this.options()[0].value
            }
            this.default = matchedOption;
        },

        onChangeTime: function () {
            var self = this, currInterval;

            if (this.options().length > 0) {
                deliveryDateList.currentDate().time_intervals.forEach(function (timeSlot) {
                    if (self.value() === timeSlot.id) {
                        currInterval = timeSlot;
                    }
                })
                if (currInterval) {
                    deliveryDateList.currentTimeSlot(currInterval);
                }
                this.validateSelect();
                /**
                 * Amasty Checkout compatibility
                 */
                if (window.am_osc_enabled) {
                    setShippingInformationAction();
                }
            }
        },

        timeOptionsText: function (timeInterval) {
            var optionLabel = timeInterval.interval_formatted,
                formattedPrice = '';

            optionLabel += ' ' + timeInterval.label;
            if (!timeInterval.fee_amount_excl_tax) {
                return optionLabel;
            }
            if (window.checkoutConfig.isDisplayShippingPriceExclTax) {
                formattedPrice = priceUtils.formatPrice(timeInterval.fee_amount_excl_tax, quote.getPriceFormat());
            } else if (window.checkoutConfig.isDisplayShippingBothPrices
                && timeInterval.fee_amount_incl_tax !== timeInterval.fee_amount_excl_tax
            ) {
                formattedPrice = priceUtils.formatPrice(timeInterval.fee_amount_incl_tax, quote.getPriceFormat())
                    + ' ' + $t('Excl. Tax') + ': +'
                    + priceUtils.formatPrice(timeInterval.fee_amount_excl_tax, quote.getPriceFormat());
            } else {
                formattedPrice = priceUtils.formatPrice(timeInterval.fee_amount_incl_tax, quote.getPriceFormat());
            }

            optionLabel += ' (+' + formattedPrice + ')';
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
