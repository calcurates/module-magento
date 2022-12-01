/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'jquery',
    'mage/utils/wrapper',
    'underscore',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/quote',
    'mage/storage',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/shipping-save-processor/payload-extender'
], function ($, wrapper, _, resourceUrlManager, quote, storage, totalsService, errorProcessor, payloadExtender) {
    'use strict';

    return function (callbacks, deferred) {
        var serviceUrl = resourceUrlManager.getUrlForTotalsEstimationForNewAddress(quote),
            payload,
            requiredFields = ['countryId', 'region', 'regionId', 'postcode'],
            address = quote.isVirtual() ? quote.billingAddress() : quote.shippingAddress(),
            deferredObject = deferred || $.Deferred();

        address = _.pick(address, requiredFields);

        payload = {
            addressInformation: {
                address: address
            }
        };

        payloadExtender(payload)

        if (quote.shippingMethod() && quote.shippingMethod()['method_code']) {
            payload.addressInformation['shipping_method_code'] = quote.shippingMethod()['method_code'];
            payload.addressInformation['shipping_carrier_code'] = quote.shippingMethod()['carrier_code'];
        }

        totalsService.isLoading(true);

        return storage.post(
            serviceUrl,
            JSON.stringify(payload),
            false
        ).done(function (response) {
            var proceed = true;

            if (callbacks && callbacks.length > 0) {
                _.each(callbacks, function (callback) {
                    proceed = proceed && callback();
                });
            }

            if (proceed) {
                quote.setTotals(response);
                deferredObject.resolve();
            }
        }).fail(function (response) {
            if (response.responseText || response.status) {
                errorProcessor.process(response);
            }

            deferredObject.reject();
        }).always(function () {
            totalsService.isLoading(false);
        });
    };
});
