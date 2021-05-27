/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([], function () {
    'use strict';

    /**
     * Parse rates and update stores settings
     *
     * @param {String} methodCode
     * @return {Object.<{type: String, id: String, sub_id: String}>}
     */
    return function (methodCode) {
        var parts = methodCode ? methodCode.split('_') : [];

        return {
            type: parts[0] || '',
            id: parts[1] || '',
            sub_id: parts[2] || ''
        };
    };
});
