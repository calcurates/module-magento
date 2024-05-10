/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    "ko",
    "uiRegistry",
    "Calcurates_ModuleMagento/js/model/delivery-date/delivery-date-list",
    "Magento_Checkout/js/model/quote",
    "Calcurates_ModuleMagento/js/model/shipping-save-processor/split-checkout-shipments",
    "Magento_Checkout/js/model/shipping-service",
    "Magento_Checkout/js/checkout-data",
    "mage/translate",
    "underscore",
], function (ko, registry, deliveryDateList, quote, splitCheckoutShipments, shippingService, checkoutData, $t, _) {
    "use strict"

    return function (Component) {
        return Component.extend({
            defaults: {
                splitCheckoutShipments: {},
            },
            errorValidationMessage: ko.observable(false),

            /**
             * @returns {*}
             */
            initialize: function () {
                var self = this
                shippingService.getShippingRates().subscribe(this.initSplitCheckoutShipments.bind(this))
                this._super()
                quote.shippingMethod.subscribe(function () {
                    self.errorValidationMessage(false)
                })

                registry.async("checkoutProvider")(function (checkoutProvider) {
                    checkoutProvider.on("shippingAddress", function (shippingAddrsData) {
                        /**
                         * Save shipping address to localStorage if no street
                         */
                        if (
                            (!shippingAddrsData.street || _.isEmpty(shippingAddrsData.street[0])) &&
                            shippingAddrsData.postcode
                        ) {
                            checkoutData.setShippingAddressFromData(shippingAddrsData)
                        }
                    })
                })
                return this
            },

            /**
             * Init observables for split shipping method's radio buttons
             * @param methods
             */
            initSplitCheckoutShipments: function (methods) {
                var self = this,
                    hasPreSelected = false,
                    metaMethod = methods.filter(function (method) {
                        return self.isSplitCheckout(method)
                    }),
                    selectedSplitCheckoutShipments = checkoutData.getSelectedSplitCheckoutShipments(),
                    isSavedMetarate = checkoutData.getSelectedShippingRate() === "calcurates_metarate"

                if (metaMethod.length) {
                    metaMethod[0].extension_attributes.calcurates_metarate_data.forEach(function (item) {
                        if (typeof self.splitCheckoutShipments[item.origin_id] === "function") {
                            return
                        }
                        if (!isSavedMetarate && item.rates && item.rates.length === 1) {
                            selectedSplitCheckoutShipments[item.origin_id] = item.rates[0].method_code
                            hasPreSelected = true
                        }
                        self.splitCheckoutShipments[item.origin_id] = ko.observable(
                            selectedSplitCheckoutShipments &&
                                selectedSplitCheckoutShipments[item.origin_id] &&
                                (isSavedMetarate || hasPreSelected)
                                ? selectedSplitCheckoutShipments[item.origin_id]
                                : null
                        )
                        checkoutData.setSelectedSplitCheckoutShipments(selectedSplitCheckoutShipments)
                    })
                    checkoutData.setSelectedShippingRate("calcurates_metarate")
                    splitCheckoutShipments(this.splitCheckoutShipments)
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
                var self = this,
                    superResult = this._super()

                if (superResult) {
                    if (
                        "undefined" !== typeof deliveryDateList.currentDeliveryDatesList() &&
                        deliveryDateList.currentDeliveryDatesList().length > 0
                    ) {
                        var dateSelect = registry.get("index = calcurates-delivery-date-date"),
                            timeSelect = registry.get("index = calcurates-delivery-date-time")

                        var dateSelectValidationResult = dateSelect.validateSelect(),
                            timeSelectValidationResult = timeSelect.validateSelect()

                        if (!dateSelectValidationResult || !timeSelectValidationResult) {
                            return false
                        }
                    }
                    if (quote.shippingMethod() && quote.shippingMethod()["method_code"] === "metarate") {
                        _.each(this.splitCheckoutShipments, function (observable) {
                            if (observable() === null) {
                                self.errorValidationMessage(
                                    $t("The shipping method is missing. Select the shipping method and try again.")
                                )
                                superResult = false
                            }
                        })
                    }
                }
                return superResult
            },

            /**
             * Check if we have enough data to show split checkout
             * @param method
             * @returns {boolean|*|boolean}
             */
            isSplitCheckout: function (method) {
                return (
                    method.carrier_code === "calcurates" &&
                    method.extension_attributes &&
                    method.extension_attributes.calcurates_metarate_data
                )
            },

            /**
             * Get quote items for split checkout shipment
             * @param shipment
             * @returns {*}
             */
            getShipmentQuoteItems: function (shipment) {
                let items = quote.totals().items,
                    bundleItems = [],
                    result = []

                items.forEach(function (item) {
                    if (item.extension_attributes && item.extension_attributes.bundle_children) {
                        bundleItems.push(...item.extension_attributes.bundle_children)
                    }
                })
                result.push(...items, ...bundleItems)

                result = result.filter(function (item) {
                    return shipment.products.includes(parseInt(item.item_id))
                })

                result.forEach(function (item) {
                    let originQty = shipment.product_qtys.find(function (qtyItem) {
                        return !!qtyItem[item.item_id]
                    })
                    item.qty = (originQty && originQty[item.item_id]) ? originQty[item.item_id] : item.qty
                })

                return result
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
                return true
            },

            /**
             * Get tooltip position
             * @returns {*|string}
             */
            getInfoMessagePosition: function () {
                return window.checkoutConfig.calcurates &&
                    window.checkoutConfig.calcurates.info_message_display_position
                    ? window.checkoutConfig.calcurates.info_message_display_position
                    : "in_tooltip"
            },
        })
    }
})
