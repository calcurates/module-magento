/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

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
