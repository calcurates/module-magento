/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'underscore',
    'uiRegistry',
    'mage/utils/wrapper',
    './split-checkout-shipments'
], function (_, registry, wrapper, splitCheckoutShipments) {
    'use strict';

    return function (payloadExtender) {
        return wrapper.wrap(payloadExtender, function (original, payload) {
            var payloadOriginal = original(payload),
                deliveryDateInfo = registry.get('checkoutProvider').get('calcurates_delivery_date'),
                splitShipments = {};
            if (_.isUndefined(payloadOriginal.addressInformation.extension_attributes)) {
                payloadOriginal.addressInformation.extension_attributes = {};
            }

            payloadOriginal.addressInformation.extension_attributes.calcurates_delivery_date_id
                = deliveryDateInfo.calcurates_delivery_date_id;
            payloadOriginal.addressInformation.extension_attributes.calcurates_delivery_date_time_id
                = deliveryDateInfo.calcurates_delivery_date_time_id;

            _.each(splitCheckoutShipments(), function (value, key) {
                splitShipments[key] = value()
            })
            payloadOriginal.addressInformation.extension_attributes.calcurates_split_shipments = splitShipments

            return payloadOriginal;
        });
    };
});
