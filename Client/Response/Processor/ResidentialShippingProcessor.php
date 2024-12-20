<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor;

use Calcurates\ModuleMagento\Api\SalesData\QuoteData\QuoteAddressExtensionAttributesInterfaceFactory;
use Calcurates\ModuleMagento\Client\Response\ResponseProcessorInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Shipping\Model\Rate\Result;

class ResidentialShippingProcessor implements ResponseProcessorInterface
{
    /**
     * @var QuoteAddressExtensionAttributesInterfaceFactory
     */
    private $quoteAddressFactory;

    /**
     * ResidentialShippingProcessor constructor.
     * @param QuoteAddressExtensionAttributesInterfaceFactory $quoteAddressFactory
     */
    public function __construct(
        QuoteAddressExtensionAttributesInterfaceFactory $quoteAddressFactory
    ) {
        $this->quoteAddressFactory = $quoteAddressFactory;
    }

    /**
     * @param Result $result
     * @param array $response
     * @param CartInterface $quote
     * @return void
     */
    public function process(Result $result, array &$response, CartInterface $quote): void
    {
        if (isset($response['metadata'])
            && array_key_exists('shipToResidentialIndicator', $response['metadata'])
            && $quote->getShippingAddress()
            && $quote->getShippingAddress()->getAddressId()
        ) {
            switch ($response['metadata']['shipToResidentialIndicator']) {
                case 'yes':
                    $shipToResidentialIndicator = 1;
                    break;
                case 'no':
                    $shipToResidentialIndicator = 2;
                    break;
                default:
                    $shipToResidentialIndicator = null;
            }
            $this->quoteAddressFactory->create()->setsetAddressId($quote->getShippingAddress()->getAddressId())
                ->setResidentialDelivery($shipToResidentialIndicator);
            $addressExtensionAttributes = $quote->getShippingAddress()->getExtensionAttributes();
            $addressExtensionAttributes->setResidentialDelivery(
                $this->quoteAddressFactory->create()
                    ->setAddressId((int) $quote->getShippingAddress()->getAddressId())
                    ->setResidentialDelivery($shipToResidentialIndicator)
            );
            $quote->getShippingAddress()->setExtensionAttributes($addressExtensionAttributes);
        }
    }
}
