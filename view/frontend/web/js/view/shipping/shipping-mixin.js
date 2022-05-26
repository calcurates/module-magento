/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'ko',
    'uiRegistry',
    'Calcurates_ModuleMagento/js/model/delivery-date/delivery-date-list',
    'Magento_Checkout/js/model/quote',
    'Calcurates_ModuleMagento/js/model/shipping-save-processor/split-checkout-shipments',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/checkout-data'
], function (
    ko,
    registry,
    deliveryDateList,
    quote,
    splitCheckoutShipments,
    shippingService,
    checkoutData
) {
    'use strict';

    return function (Component) {
        return Component.extend({

            defaults: {
                splitCheckoutShipments: {}
            },

            /**
             * @returns {*}
             */
            initialize: function () {
                this._super();
                shippingService.getShippingRates().subscribe(this.initSplitCheckoutShipments.bind(this))
                return this;
            },

            /**
             * Init observables for split shipping method's radio buttons
             * @param methods
             */
            initSplitCheckoutShipments: function (methods) {
                var self = this,
                    metaMethod = methods.filter(function (method) {
                        return self.isSplitCheckout(method)
                    }),
                    selectedSplitCheckoutShipments = checkoutData.getSelectedSplitCheckoutShipments(),
                    isSavedMetarate = checkoutData.getSelectedShippingRate() === 'calcurates_metarate'

                if (metaMethod.length) {
                    metaMethod[0].extension_attributes.calcurates_metarate_data.forEach(function (item) {
                        if (typeof self.splitCheckoutShipments[item.origin_id] === 'function') {
                            return
                        }
                        self.splitCheckoutShipments[item.origin_id] = ko.observable(
                            selectedSplitCheckoutShipments
                            && selectedSplitCheckoutShipments[item.origin_id]
                            && isSavedMetarate
                                ? selectedSplitCheckoutShipments[item.origin_id]
                                : null
                        )
                    })
                }
            },

            /**
             * Reset split shipping method's radio buttons observables
             */
            resetSplitCheckoutShipments: function () {
                _.each(this.splitCheckoutShipments, function (observable) {
                    observable(null)
                })
                checkoutData.setSelectedSplitCheckoutShipments({})
            },

            /**
             * @return {Boolean}
             */
            validateShippingInformation: function () {
                var superResult = this._super();

                if (superResult) {
                    if ('undefined' !== typeof deliveryDateList.currentDeliveryDatesList()
                        && deliveryDateList.currentDeliveryDatesList().length > 0
                    ) {
                        var dateSelect = registry.get('index = calcurates-delivery-date-date'),
                            timeSelect = registry.get('index = calcurates-delivery-date-time');

                        var dateSelectValidationResult = dateSelect.validateSelect(),
                            timeSelectValidationResult = timeSelect.validateSelect();

                        if (!dateSelectValidationResult || !timeSelectValidationResult) {
                            return false;
                        }
                    }
                }
                return superResult;
            },

            /**
             * Check if we have enough data to show split checkout
             * @param method
             * @returns {boolean|*|boolean}
             */
            isSplitCheckout: function (method) {
                return method.carrier_code === 'calcurates'
                    && method.extension_attributes
                    && method.extension_attributes.calcurates_metarate_data
            },

            /**
             * Get quote items for split checkout shipment
             * @param shipment
             * @returns {*}
             */
            getShipmentQuoteItems: function (shipment) {
                return quote.totals().items.filter(function (item) {
                    return shipment.products.includes(parseInt(item.item_id))
                });
            },

            /**
             * Save shipment method
             * @param method
             * @param metaMethod
             * @param parentMethod
             */
            selectSplitCheckoutMethod: function (method, metaMethod, parentMethod) {
                this.splitCheckoutShipments[metaMethod.origin_id](method.method_code)
                splitCheckoutShipments(this.splitCheckoutShipments)
                checkoutData.setSelectedSplitCheckoutShipments(this.splitCheckoutShipments)
                this.selectShippingMethod(parentMethod)
            },

            /**
             * Invoke split checkout method if needed
             * @param method
             * @param parent
             * @param grandParent
             */
            handleSelectShippingMethod: function (method, parent, grandParent) {
                if (parent.origin_id) {
                    this.selectSplitCheckoutMethod(method, parent, grandParent)
                } else {
                    this.resetSplitCheckoutShipments()
                    this.selectShippingMethod(method)
                }
                return true;
            }
        });
    };
});
