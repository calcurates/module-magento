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
            caption: $t('Choose a Delivery Date...'),
            template: 'Calcurates_ModuleMagento/delivery-date/date-select',
            label: $t('Delivery Date'),
            additionalCss: '',
            validationError: '',
            timeSlotDateRequired: null,
            defaultValueType: 'earliest',
        },

        initObservable: function () {
            this._super().observe(['options', 'timeSlotDateRequired', 'additionalCss', 'validationError']);

            deliveryDateList.currentDeliveryDatesList.subscribe(this.onCurrentDeliveryDatesListChange, this);
            this.onCurrentDeliveryDatesListChange(deliveryDateList.currentDeliveryDatesList());
            return this;
        },

        onCurrentDeliveryDatesListChange: function (data) {
            var options = [];
            this.timeSlotDateRequired(this.getTimeSlotDateRequired(deliveryDateList));
            this.additionalCss(this.getAdditionalCss());

            data.forEach(function (deliveryDate) {
                options.push({
                    value: deliveryDate.id,
                    label: this.dateText(deliveryDate)
                })
            }.bind(this));
            if (this.timeSlotDateRequired()) {
                this.caption(null);
            }
            this.updateCurrentDate(data);
            this.setOptions(options);
            this.setDefaultOption(data);
            this.updateValue();
            this.onChangeDate();
        },

        updateValue: function () {
            if (deliveryDateList.currentDate() && deliveryDateList.currentDate().id) {
                this.value(deliveryDateList.currentDate().id);
                this.value.valueHasMutated();
                return;
            }
            if (this.timeSlotDateRequired() && this.options().length) {
                this.value(this.default);
            }
        },

        updateCurrentDate: function (data) {
            var currDate = deliveryDateList.currentDate, newDateItem

            if (data.length && currDate() && currDate().date) {
                data.forEach(function (dateSlot) {
                    if (dateSlot.date.slice(0,10) === currDate().date.slice(0,10)) {
                        newDateItem = dateSlot;
                    }
                })
            }
            if (newDateItem && currDate().id !== newDateItem.id) {
                currDate(newDateItem);
            } else if (!newDateItem) {
                currDate({});
            }
        },

        setDefaultOption: function (data) {
            var matchedOption
            if (!data.length) {
                return;
            }
            if (this.defaultValueType === 'earliest_cheapest') {
                data.sort(function (a, b) {
                    return Date.parse(a.date) - Date.parse(b.date);
                })
                matchedOption = data.reduce(function (prev, curr) {
                    return (prev.fee_amount > curr.fee_amount) ? curr : prev;
                })
                matchedOption = matchedOption.id;
            } else {
                matchedOption = this.options()[0].value
            }
            this.default = matchedOption;
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
            if ('undefined' !== typeof deliveryDateList.currentDeliveryDatesList()
                && deliveryDateList.currentDeliveryDatesList().length > 0
            ) {
                this.validateSelect();
                /**
                 * Amasty Checkout compatibility
                 */
                if (window.am_osc_enabled) {
                    setShippingInformationAction();
                }
            }
        },

        dateText: function (deliveryDate) {
            var optionLabel = deliveryDate.date_formatted,
                formattedPrice = '';

            optionLabel += ' ' + deliveryDate.label;
            if (!deliveryDate.fee_amount_excl_tax) {
                return optionLabel;
            }
            if (window.checkoutConfig.isDisplayShippingPriceExclTax) {
                formattedPrice = priceUtils.formatPrice(deliveryDate.fee_amount_excl_tax, quote.getPriceFormat());
            } else if (window.checkoutConfig.isDisplayShippingBothPrices
                && deliveryDate.fee_amount_incl_tax !== deliveryDate.fee_amount_excl_tax
            ) {
                formattedPrice = priceUtils.formatPrice(deliveryDate.fee_amount_incl_tax, quote.getPriceFormat())
                    + ' ' + $t('Excl. Tax') + ': +'
                    + priceUtils.formatPrice(deliveryDate.fee_amount_excl_tax, quote.getPriceFormat());
            } else {
                formattedPrice = priceUtils.formatPrice(deliveryDate.fee_amount_incl_tax, quote.getPriceFormat());
            }

            optionLabel += ' (+' + formattedPrice + ')';

            return optionLabel;
        },

        getTimeSlotDateRequired: function () {
            var deliveryDatesMetadata = deliveryDateList.getDeliveryDatesMetadata();

            return !!(deliveryDatesMetadata
                && deliveryDatesMetadata.time_slot_date_required === true);
        },

        getAdditionalCss: function () {
            var initialClasses = '';
            if (this.timeSlotDateRequired()) {
                initialClasses += ' _required';
            }
            if (this.validationError()) {
                initialClasses += ' _error';
            }
            return initialClasses;
        },

        validateSelect: function () {
            if (this.getTimeSlotDateRequired() === true
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
