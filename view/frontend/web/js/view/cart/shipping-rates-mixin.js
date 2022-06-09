/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'underscore',
    'mage/utils/objects',
    'Magento_Checkout/js/checkout-data'
], function (_, utils, checkoutData) {
    'use strict';

    return function (ShippingRates) {
        return ShippingRates.extend({

            defaults: {
                template: 'Calcurates_ModuleMagento/cart/shipping-rates'
            },

            selectShippingMethod: function (methodData) {
                checkoutData.setSelectedSplitCheckoutShipments(
                    this.getMinimumSplitShipmentCodes(methodData)
                )
                return this._super(methodData)
            },

            /**
             * Get split shipments with minimum amount for each origin
             * @param methodData
             * @returns {{}}
             */
            getMinimumSplitShipments: function (methodData) {
                var metaData = {}

                if (methodData.carrier_code === 'calcurates'
                    && methodData.method_code === 'metarate'
                    && methodData.extension_attributes
                    && methodData.extension_attributes.calcurates_metarate_data
                    && methodData.extension_attributes.calcurates_metarate_data.length
                ) {
                    methodData.extension_attributes.calcurates_metarate_data.forEach(function (rateData) {
                        metaData[rateData.origin_id] = _.min(rateData.rates, function (rate) {
                            return rate.amount
                        })
                    })
                }
                return metaData
            },

            /**
             * Get codes of split shipments with minimum amount for each origin
             * @param methodData
             * @returns {{}}
             */
            getMinimumSplitShipmentCodes: function (methodData) {
                var metaCodes = {}

                _.each(this.getMinimumSplitShipments(methodData), function (rate, key) {
                    metaCodes[key] = rate.method_code
                })

                return metaCodes
            },

            /**
             * Calculate minimum possible amount of aggregated shipment
             * @param shippingRateGroupTitle
             * @returns {*}
             */
            getRatesForGroup: function (shippingRateGroupTitle) {
                var ratesForGroup = utils.hardCopy(this._super(shippingRateGroupTitle)),
                    self = this

                ratesForGroup.forEach(function (rate) {
                    _.each(self.getMinimumSplitShipments(rate), function (shipment) {
                        rate.amount += shipment.amount
                        rate.price_incl_tax += shipment.price_incl_tax
                        rate.price_excl_tax += shipment.price_excl_tax
                    })
                })
                return ratesForGroup
            },

        })
    }
})
