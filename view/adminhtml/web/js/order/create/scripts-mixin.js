/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict'

    return function () {
        if (typeof window.AdminOrder !== 'undefined') {
            var originalSetShippingMethod = AdminOrder.prototype.setShippingMethod,
                originalLoadArea = AdminOrder.prototype.loadArea

            AdminOrder.prototype.loadArea = function (area, indicator, params) {
                if (!params || !$('.metarates-wrapper').length) {
                    return originalLoadArea.apply(this, arguments)
                }
                if (_.contains(area, 'shipping_method')) {
                    params['collect_shipping_rates'] = 1
                }
                if (params['order[shipping_method]'] === 'calcurates_metarate') {
                    $('.metarates-wrapper select').each(function () {
                        params['calcurates_split_shipments[' + $(this).data('origin') + ']'] = $(this).val()
                    })
                }
                return originalLoadArea.apply(this, arguments)
            }

            AdminOrder.prototype.setShippingMethod = function (method) {
                if (method === 'calcurates_metarate') {
                    $('.metarates-wrapper').show().find('select').removeClass('ignore-validate')
                    if (!this.validateMetarates()) {
                        return false
                    }
                }
                originalSetShippingMethod.apply(this, arguments)
            }

            AdminOrder.prototype.validateMetarates = function () {
                var isValid = true
                $('.metarates-wrapper select').each(function () {
                    if ($(this).val() === '') {
                        isValid = false
                    }
                })
                return isValid
            }
        }
    }
})
