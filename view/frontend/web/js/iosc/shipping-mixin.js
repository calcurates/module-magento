define([], function () {
    return function (IoscShipping) {
        return IoscShipping.extend({
            /**
             * Don't use default rates for default address, only query against currently selected address
             * @return {*[]}
             */
            getMethods: function () {
                return []
            },
        })
    }
})
