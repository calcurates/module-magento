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
