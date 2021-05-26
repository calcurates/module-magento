define([], function () {
    'use strict';

    /**
     * Parse rates and update stores settings
     *
     * @param {String} methodCode
     * @return {Object.<{type: String, id: String, sub_id: String}>}
     */
    return function (methodCode) {
        var parts = methodCode.split('_');

        return {
            type: parts[0] || '',
            id: parts[1] || '',
            sub_id: parts[2] || ''
        };
    };
});