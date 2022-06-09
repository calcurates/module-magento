/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'underscore',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/quote',
    'mage/storage',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/cart/cache',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/checkout-data'
], function (
    _,
    resourceUrlManager,
    quote,
    storage,
    totalsService,
    errorProcessor,
    cartCache,
    customerData,
    checkoutData
) {
    return function (Default) {
        return _.extend(Default, {

            estimateTotals: function (address) {
                return this.loadFromServer(address)
            },

            preparePayload: function (address) {
                var payload = {
                        addressInformation: {
                            address: _.pick(address, cartCache.requiredFields)
                        }
                    },
                    selectedShipments = [];

                if (quote.shippingMethod() && quote.shippingMethod()['method_code']) {
                    payload.addressInformation['shipping_method_code'] = quote.shippingMethod()['method_code']
                    payload.addressInformation['shipping_carrier_code'] = quote.shippingMethod()['carrier_code']
                }

                _.each(checkoutData.getSelectedSplitCheckoutShipments(), function (code, origin) {
                    selectedShipments.push({
                        origin: origin,
                        method: code
                    })
                })

                payload.addressInformation.extension_attributes = {
                    'calcurates_split_shipments' : selectedShipments
                }

                return payload;
            },

            loadFromServer: function (address) {
                var serviceUrl,
                    payload;

                // Start loader for totals block
                totalsService.isLoading(true)
                serviceUrl = resourceUrlManager.getUrlForTotalsEstimationForNewAddress(quote)
                payload = this.preparePayload(address)

                return storage.post(
                    serviceUrl, JSON.stringify(payload), false
                ).done(function (result) {
                    var data = {
                        totals: result,
                        address: address,
                        cartVersion: customerData.get('cart')()['data_id'],
                        shippingMethodCode: null,
                        shippingCarrierCode: null
                    };

                    if (quote.shippingMethod() && quote.shippingMethod()['method_code']) {
                        data.shippingMethodCode = quote.shippingMethod()['method_code']
                        data.shippingCarrierCode = quote.shippingMethod()['carrier_code']
                    }

                    quote.setTotals(result)
                    cartCache.set('cart-data', data)
                }).fail(function (response) {
                    errorProcessor.process(response)
                }).always(function () {
                    // Stop loader for totals block
                    totalsService.isLoading(false)
                });
            }
        })
    }
})
