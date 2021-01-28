<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */
declare(strict_types=1);

namespace Calcurates\ModuleMagento\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\AdminOrder\Create;

class QuoteSaveWhileEditOrderObserver implements ObserverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    public function __construct(CartRepositoryInterface $quoteRepository)
    {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Main problem: when we collect rates from calcurates - we need quoteItemId.
     * But for edit order - we collect rates before saving items, and don't have item ids.
     * Solution: save quote with items before collecting shipping rates
     *
     * Event sales_convert_order_to_quote
     * @param Observer $observer
     * @return void
     * @see Create::initFromOrder()
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getData('quote');

        // backup old value
        $totalsCollectedFlag = $quote->getTotalsCollectedFlag();

        // just save quote without recollecting totals
        $quote->setTotalsCollectedFlag(true);
        $this->quoteRepository->save($quote);

        // restore old value
        $quote->setTotalsCollectedFlag($totalsCollectedFlag);
    }
}
