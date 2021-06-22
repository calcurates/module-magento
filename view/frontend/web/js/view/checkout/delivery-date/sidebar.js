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
