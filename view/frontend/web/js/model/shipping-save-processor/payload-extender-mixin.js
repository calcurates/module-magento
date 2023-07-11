/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    "underscore",
    "uiRegistry",
    "mage/utils/wrapper",
    "./split-checkout-shipments",
    "Calcurates_ModuleMagento/js/model/delivery-date/delivery-date-list",
], function (_, registry, wrapper, splitCheckoutShipments, deliveryDateList) {
    "use strict"

    return function (payloadExtender) {
        return wrapper.wrap(payloadExtender, function (original, payload) {
            var payloadOriginal = original(payload),
                deliveryDateInfo = registry.get("checkoutProvider").get("calcurates_delivery_date"),
                splitShipments = {},
                splitShipment = {},
                index = 0
            if (_.isUndefined(payloadOriginal.addressInformation.extension_attributes)) {
                payloadOriginal.addressInformation.extension_attributes = {}
            }

            if (deliveryDateList.currentDate()) {
                payloadOriginal.addressInformation.extension_attributes.calcurates_delivery_date_id =
                    deliveryDateInfo.calcurates_delivery_date_id
                payloadOriginal.addressInformation.extension_attributes.calcurates_delivery_date_time_id =
                    deliveryDateInfo.calcurates_delivery_date_time_id
            }

            _.each(splitCheckoutShipments(), function (value, key) {
                if (value()) {
                    splitShipment = {
                        origin: key,
                        method: value(),
                    }
                    splitShipments[index++] = splitShipment
                }
            })
            payloadOriginal.addressInformation.extension_attributes.calcurates_split_shipments = splitShipments

            return payloadOriginal
        })
    }
})
