/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'uiRegistry',
    'Calcurates_ModuleMagento/js/model/delivery-date/delivery-date-list'
], function (registry, deliveryDateList) {
    'use strict';

    return function (Component) {
        return Component.extend({

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
            }
        });
    };
});