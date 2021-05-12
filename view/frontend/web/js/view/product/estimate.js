/**
 * @author Calcurates Team
 * @copyright Copyright © 2019 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'ko',
    'jquery',
    'uiElement',
    'Calcurates_ModuleMagento/js/model/product/rates',
    'loader'
], function (ko, $, Element, ratesModel) {
    'use strict';

    return Element.extend({
        defaults: {
            rates: [],
            storeCode: '',
            productId: 0,
            fallbackMessage: '',
            isLoggedIn: 0,
            selectors: {
                addToCartForm: 'form#product_addtocart_form',
                ratesContainer: '[data-calcurates-js="rates-container"]'
            },
            nodes: {
                ratesContainer: null,
                addToCartForm: null
            },
            rateTemplatesMap: {
                free_shipping: 'Calcurates_ModuleMagento/product/rate/default',
                flat_rate: 'Calcurates_ModuleMagento/product/rate/default',
                table_rates: 'Calcurates_ModuleMagento/product/rate/default',
                in_store_pickup: 'Calcurates_ModuleMagento/product/rate/default',
            }
        },

        initialize: function () {
            this._super();

            this._initNodes();

            return this;
        },

        initObservable: function () {
            this._super();

            this.rates = ratesModel.rates;

            return this;
        },

        getRateTemplate: function (rate) {
            return this.rateTemplatesMap[rate.type];
        },

        _initNodes: function () {
            this.nodes.ratesContainer = $(this.selectors.ratesContainer);
            this.nodes.addToCartForm = $(this.selectors.ratesContainer);

            ratesModel.loadLocations(this.storeCode, [this.productId], this.isLoggedIn);
        },
    });

});
