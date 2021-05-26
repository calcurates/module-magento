define([
    'jquery'
], function (
    $
) {
    'use strict';

    var waitForElement = function (selector, callback) {
        var element = $(selector);

        if (element.length > 0) {
            callback(element);
        } else {
            setTimeout(function (){
                waitForElement(selector, callback);
            }, 200);
        }
    };

    return waitForElement;
});
