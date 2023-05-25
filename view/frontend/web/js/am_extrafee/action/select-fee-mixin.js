/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    "underscore",
    "mage/utils/wrapper",
    "Magento_Checkout/js/model/quote",
    "Magento_Checkout/js/model/shipping-rate-processor/new-address",
    "Magento_Checkout/js/model/shipping-rate-processor/customer-address",
    "Magento_Checkout/js/model/shipping-rate-registry",
], function (_, wrapper, quote, defaultProcessor, customerProcessor, rateRegistry) {
    return function (target) {
        target.extraFeeTotalString = false

        target.getExtraFeeTotal = function () {
            let totalSegments = quote.totals()["total_segments"],
                feeSegment = totalSegments.find(function (segment) {
                    return segment.code === "amasty_extrafee"
                })
            return feeSegment.extension_attributes.tax_amasty_extrafee_details.items
        }

        target.isFirstLoad = true

        target.requestHandler = wrapper.wrapSuper(target.requestHandler, function (result) {
            this._super(result)
            if (this.isFirstLoad) {
                this.isFirstLoad = false
                this.extraFeeTotalString = this.getExtraFeeTotal()
                return
            }
            if (this.extraFeeTotalString === this.getExtraFeeTotal()) {
                return
            }
            let address = quote.shippingAddress()
            rateRegistry.set(address.getCacheKey(), false)
            address.getType() === "customer-address"
                ? customerProcessor.getRates(address)
                : defaultProcessor.getRates(address)
            this.extraFeeTotalString = this.getExtraFeeTotal()
        })
        return target
    }
})
