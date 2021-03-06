/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'underscore',
    'uiRegistry',
    'mage/utils/wrapper'
], function (_, registry, wrapper) {
    'use strict';

    return function (payloadExtender) {
        return wrapper.wrap(payloadExtender, function (original, payload) {
            var payloadOriginal = original(payload),
                deliveryDateInfo = registry.get('checkoutProvider').get('calcurates_delivery_date');
            if (_.isUndefined(payloadOriginal.addressInformation.extension_attributes)) {
                payloadOriginal.addressInformation.extension_attributes = {};
            }

            payloadOriginal.addressInformation.extension_attributes.calcurates_delivery_date_id
                = deliveryDateInfo.calcurates_delivery_date_id;
            payloadOriginal.addressInformation.extension_attributes.calcurates_delivery_date_time_id
                = deliveryDateInfo.calcurates_delivery_date_time_id;

            return payloadOriginal;
        });
    };
});
