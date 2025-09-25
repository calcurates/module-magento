/**
 * @author Calcurates Team
 * @copyright Copyright © 2019 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'ko',
    'jquery',
    'underscore',
    'uiElement',
    'Calcurates_ModuleMagento/js/model/product/rates',
    'mage/translate',
    'mage/template',
    'loader'
], function (ko, $, _, Element, ratesModel, $t, mageTemplate) {
    'use strict'

    return Element.extend({
        defaults: {
            rates: [],
            currentAddress: {},
            storeCode: '',
            productId: 0,
            fallbackMessage: '',
            isLoggedIn: 0,
            googlePlacesEnabled: false,
            googlePlacesApiKey: null,
            googlePlacesAutocompleteInstance: null,
            placesAutocompleteUrl: 'https://maps.googleapis.com/maps/api/js?key=API_KEY&libraries=places&callback=initAutocomplete',
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
                default_rate: 'Calcurates_ModuleMagento/product/rate/default'
            },
            timeTmplString: '',
            countDowns: [],
            tracks: {
                currentAddress: true,
                productId: true
            },
            listens: {
                currentAddress: 'loadLocations',
                productId: 'loadLocations'
            }
        },

        initialize: function () {
            this._super()
            window.initAutocomplete = this.initPlacesAutocompleteFields.bind(this)
            this._initNodes()
            $('.calcurates-estimate-block').on('calcurates.estimate', function (event, productId) {
                this.productId = productId
            }.bind(this))
            if (!this.usePlacesAutocomplete()) {
                this.loadLocations()
            }
            return this
        },

        initObservable: function () {
            this._super()

            this.rates = ratesModel.rates
            ratesModel.rates.subscribe(function () {
                this.countDowns.map(clearInterval)
            }.bind(this))

            return this
        },

        getRateTemplate: function (rate) {
            return this.rateTemplatesMap[rate.type] || this.rateTemplatesMap['default_rate']
        },

        _initNodes: function () {
            this.nodes.ratesContainer = $(this.selectors.ratesContainer)
            this.nodes.addToCartForm = $(this.selectors.ratesContainer)
        },

        usePlacesAutocomplete: function () {
            return this.googlePlacesEnabled && this.googlePlacesApiKey
        },

        initPlacesAutocomplete: function () {
            if (this.usePlacesAutocomplete()) {
                require([this.placesAutocompleteUrl.replace('API_KEY', this.googlePlacesApiKey)])
            }
        },

        initPlacesAutocompleteFields: function () {
            const input = document.getElementById("placesAutocomplete")
            const options = {
                fields: ["address_components", "formatted_address", "geometry", "name"],
                types: ["address"],
            }
            this.googlePlacesAutocompleteInstance = new google.maps.places.Autocomplete(input, options)
            this.googlePlacesAutocompleteInstance.addListener('place_changed', this.onPlaceChanged.bind(this))
        },

        onPlaceChanged: function () {
            const place = this.googlePlacesAutocompleteInstance.getPlace()
            if (!place.geometry) {
                document.getElementById("placesAutocomplete").value = ''
            } else {
                document.getElementById("placesAutocomplete").value = place.formatted_address
                this.currentAddress = this.convertPlaceToAddress(place)
            }
        },

        loadLocations: function () {
            ratesModel.loadLocations(
                this.storeCode,
                [this.productId],
                this.isLoggedIn,
                !_.isEmpty(this.currentAddress) ? this.currentAddress : null
            )
        },

        convertPlaceToAddress: function (place) {
            let address = {},
                mapper = {
                    country: 'country',
                    regionCode: 'administrative_area_level_1',
                    regionName: 'administrative_area_level_1',
                    postalCode: 'postal_code',
                    city: 'locality',
                    addressLine1: 'route',
                    streetNumber: 'street_number',
                }
            if (place.address_components && _.isArray(place.address_components)) {
                place.address_components.forEach(function (component) {
                    _.each(mapper, function (value, key) {
                        if (_.contains(component.types, value)) {
                            if (key === 'country') {
                                address[key] = component.short_name
                            } else if (key === 'regionCode') {
                                address[key] = component.short_name || ''
                            } else if (key === 'regionName') {
                                address[key] = component.long_name || null
                            } else {
                                address[key] = component.long_name
                            }
                        }
                    })
                })
            }
            if (address['addressLine1'] && address['streetNumber']) {
                address.addressLine1 = address['streetNumber'] + ' ' + address['addressLine1']
            }
            delete address['streetNumber']
            return address
        },

        /**
         * Run countdown timer if needed
         * @param rate
         * @param element
         */
        runCountdown: function (rate, element) {
            if (rate['cut_off_time_hour'] === undefined
                || rate['cut_off_time_minute'] === undefined
                || rate['rendered_template'].indexOf('{countdown}') === -1
            ) {
                return
            }
            let now = new Date(),
                cutoff = new Date()

            cutoff.setUTCHours(rate['cut_off_time_hour'])
            cutoff.setUTCMinutes(rate['cut_off_time_minute'])
            if (now > cutoff) {
                cutoff.setDate(cutoff.getDate() + 1)
            }

            this.countdown(cutoff, element, rate['rendered_template'])
            this.countDowns.push(
                setInterval(
                    this.countdown.bind(this),
                    1000,
                    cutoff,
                    element,
                    rate['rendered_template']
                )
            )
        },

        /**
         * Countdown ticker
         * @param cutoff
         * @param element
         * @param initialTmpl
         */
        countdown: function (cutoff, element, initialTmpl) {
            let now = new Date(),
                interval = cutoff.getTime() - now.getTime(),
                hours = Math.floor(interval / 3600000),
                minutes = Math.floor(interval / 60000) - hours * 60,
                seconds = Math.floor(interval / 1000) - hours * 3600 - minutes * 60,
                tmpl = mageTemplate(this.timeTmplString, {
                    hours: hours,
                    minutes: minutes,
                    seconds: seconds
                })
            if (interval <= 0) {
                return
            }
            $(element).html(initialTmpl.replace('{countdown}', tmpl))
        }
    })
})
