/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'jquery'
], function ($) {
    return function (Configurable) {
        $.widget('mage.configurable', Configurable, {
            _reloadPrice: function () {
                this._super()
                if (this.simpleProduct) {
                    $('.calcurates-estimate-block').trigger('calcurates.estimate', this.simpleProduct)
                }
            }
        })
        return $.mage.configurable
    }
})
