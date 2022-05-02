/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'jquery'
], function ($) {
    return function (OrderReview) {
        $.widget('mage.orderReview', OrderReview, {
            options: {
                metaRatesWrapperSelector: '.metarates-wrapper'
            },

            /** @inheritdoc */
            _create: function () {
                this._super()
                this.toggleMetaRates()
                $(this.options.metaRatesWrapperSelector).find('select').on('change', this.onMetaRateChange.bind(this))
            },

            /** @inheritdoc */
            _submitUpdateOrder: function (url, resultId) {
                this.toggleMetaRates()
                if (!this.isSelectedMetaRates() || this.validateMetarates()) {
                    this._super(url, resultId)
                }
                if (this.isSelectedMetaRates() && !this.validateMetarates()) {
                    this._updateOrderSubmit(true)
                }
            },

            onMetaRateChange: function () {
                if (this.validateMetarates()) {
                    $(this.options.shippingSelector).trigger('change')
                    this._updateOrderSubmit(false)
                } else {
                    this._updateOrderSubmit(true)
                }
            },

            validateMetarates: function () {
                var complete = true
                $(this.options.metaRatesWrapperSelector).find('select').each(function () {
                    if (!$(this).val()) {
                        complete = false
                    }
                })
                return complete
            },

            toggleMetaRates: function () {
                this._updateOrderSubmit(this.isSelectedMetaRates() && !this.validateMetarates())
                $(this.options.metaRatesWrapperSelector).toggle(this.isSelectedMetaRates())
            },

            isSelectedMetaRates: function () {
                return $(this.options.shippingSelector).val() === 'calcurates_metarate'
            }
        })

        return $.mage.orderReview
    }
})
