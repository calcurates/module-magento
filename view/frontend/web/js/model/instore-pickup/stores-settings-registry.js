define([
    'ko'
], function (ko) {
    'use strict';

    var storesSettings = ko.observable([]);

    return {

        /**
         * Set stores settings
         *
         * @param {Array} storesSettingsData
         */
        setStoresSettings: function (storesSettingsData) {
            storesSettings(storesSettingsData);
            storesSettings.valueHasMutated();
        },

        /**
         * Get stores settings
         *
         * @returns {*}
         */
        getStoresSettings: function () {
            return storesSettings;
        }
    };
});
