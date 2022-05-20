/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'Magento_Customer/js/customer-data',
    'underscore'
], function (storage, _) {
    'use strict';

    return function (CheckoutData) {
        CheckoutData.getSelectedSplitCheckoutShipments = function () {
            return storage.get('checkout-data')().selectedSplitCheckoutShipments
        }

        CheckoutData.setSelectedSplitCheckoutShipments = function (data) {
            let storageData = {},
                obj = storage.get('checkout-data')()

            _.each(data, function (value, originId) {
                storageData[originId] = value()
            })
            obj.selectedSplitCheckoutShipments = storageData
            storage.set('checkout-data', obj);
        }
        return CheckoutData
    }
})
