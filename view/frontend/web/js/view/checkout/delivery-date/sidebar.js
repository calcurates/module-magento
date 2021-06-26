/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'uiComponent',
    'uiRegistry'
], function (Component, registry) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Calcurates_ModuleMagento/delivery-date/sidebar'
        },
        _dateComponent: null,
        _timeComponent: null,

        getDateComponent: function () {
            if (this._dateComponent === null) {
                this._dateComponent = registry.get({ index: 'calcurates-delivery-date-date' });
            }

            return this._dateComponent;
        },

        getTimeComponent: function () {
            if (this._timeComponent === null) {
                this._timeComponent = registry.get({ index: 'calcurates-delivery-date-time' });
            }

            return this._timeComponent;
        },

        getDeliveryDate: function () {
            if (this.getDateComponent()) {
                return this.getDateComponent().getPreview();
            }

            return '';
        },

        getDeliveryTime: function () {
            if (this.getTimeComponent()) {
                return this.getTimeComponent().getPreview();
            }

            return '';
        }
    });
});
