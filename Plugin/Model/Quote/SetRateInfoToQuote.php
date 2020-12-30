<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\Model\Quote;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Calcurates\ModuleMagento\Client\RatesResponseProcessor;
use Calcurates\ModuleMagento\Model\Carrier;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\TotalsCollector;

class SetRateInfoToQuote
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     *
     * @param TotalsCollector $subject
     * @param Total $total
     * @param Quote $quote
     * @return Total
     */
    public function afterCollect(
        TotalsCollector $subject,
        Total $total,
        Quote $quote
    ) {
        $address = $quote->getShippingAddress();
        $rate = $address->getShippingRateByCode($address->getShippingMethod());

        // @TODO: delivery dates is array, and on second recollect totals we lost delivery dates in rate model.
        // @TODO: possible bug - choose rate with delivery date (DD), press next, return to shipping step and choose
        // @TODO: rate without DD, and then place order. It can be wrong DD in order.
        if ($rate
            && $rate->getCarrier() === Carrier::CODE
            && $deliveryDates = $rate->getData(RatesResponseProcessor::CALCURATES_DELIVERY_DATES)
        ) {
            $quote->setData(
                CustomSalesAttributesInterface::DELIVERY_DATES,
                $this->serializer->serialize($deliveryDates)
            );
        }

        return $total;
    }
}
