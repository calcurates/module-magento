/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

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
