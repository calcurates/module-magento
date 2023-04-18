define([
    'mage/utils/wrapper'
], function (wrapper) {
    'use strict';
    return function (target) {
        var mixin = {
            /**
             * Fix case when input group children are not yet initialized
             *
             * @param {Function} original
             * @param {Object} element
             * @param {Number} delay
             */
            bindHandler: function (original, element, delay) {
                if (element.component.indexOf('/group') !== -1 && !element.elems().length) {
                    element.elems.subscribe(this.bindHandler.bind(this, element, delay))
                }
                return original(element, delay);
            }
        };

        wrapper._extend(target, mixin)
        return target
    };
});
