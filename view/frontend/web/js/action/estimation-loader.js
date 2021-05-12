/**
 * @author Calcurates Team
 * @copyright Copyright © 2019 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'jquery',
    'loader'
], function ($) {
    'use strict';

    var selectors = {
        loaderSelector: '[data-calcurates-js="rates-container"]'
    };

    return {
        show: function () {
            $(selectors.loaderSelector).loader('show');
        },

        hide: function () {
            $(selectors.loaderSelector).loader('hide');
        }
    };
});
