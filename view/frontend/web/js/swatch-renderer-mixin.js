/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'jquery'
], function ($) {
    return function (SwatchRenderer) {
        $.widget('mage.SwatchRenderer', SwatchRenderer, {
            _UpdatePrice: function () {
                this._super()
                if (this.getProductId()) {
                    $('.calcurates-estimate-block').trigger('calcurates.estimate', this.getProductId())
                }
            }
        })
        return  $.mage.SwatchRenderer
    }
})
