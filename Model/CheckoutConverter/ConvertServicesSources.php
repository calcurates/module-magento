<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\CheckoutConverter;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class ConvertServicesSources
{
    /**
     * @param Quote $quote
     * @param Order $order
     */
    public function convert(Quote $quote, Order $order): void
    {
        $order->setData(
            CustomSalesAttributesInterface::CARRIER_SOURCE_CODE_TO_SERVICE,
            $quote->getData(CustomSalesAttributesInterface::CARRIER_SOURCE_CODE_TO_SERVICE)
        );
    }
}
