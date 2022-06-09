/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

var config = {
    config: {
        mixins: {
            'Magento_Sales/order/create/scripts': {
                'Calcurates_ModuleMagento/js/order/create/scripts-mixin': true
            }
        }
    }
};
