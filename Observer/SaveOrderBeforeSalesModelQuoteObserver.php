<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Observer;

use Calcurates\ModuleMagento\Model\CheckoutConverter\QuoteToOrderConverter;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveOrderBeforeSalesModelQuoteObserver implements ObserverInterface
{
    /**
     * @var QuoteToOrderConverter
     */
    private $quoteToOrderConverter;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * SaveOrderBeforeSalesModelQuoteObserver constructor.
     * @param QuoteToOrderConverter $quoteToOrderConverter
     * @param EntityManager $entityManager
     */
    public function __construct(
        QuoteToOrderConverter $quoteToOrderConverter,
        EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->quoteToOrderConverter = $quoteToOrderConverter;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /* @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');
        /* @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        $this->quoteToOrderConverter->convert($quote, $order);
        if ($quote->getShippingAddress()
            && $quote->getShippingAddress()->getExtensionAttributes()
            && $quote->getShippingAddress()->getExtensionAttributes()->getResidentialDelivery()
        ) {
            $this->entityManager->save(
                $quote->getShippingAddress()->getExtensionAttributes()->getResidentialDelivery()
            );
        }
        return $this;
    }
}
