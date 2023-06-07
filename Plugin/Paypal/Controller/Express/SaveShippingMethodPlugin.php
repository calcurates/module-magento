<?php

namespace Calcurates\ModuleMagento\Plugin\Paypal\Controller\Express;

use Calcurates\ModuleMagento\Api\SalesData\QuoteData\GetQuoteDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\SaveQuoteDataInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\Generic;
use Magento\Paypal\Controller\Express\AbstractExpress\SaveShippingMethod;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class SaveShippingMethodPlugin
{
    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var Generic
     */
    private $paypalSession;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var GetQuoteDataInterface
     */
    private $getQuoteData;

    /**
     * @var SaveQuoteDataInterface
     */
    private $saveQuoteData;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Generic $paypalSession
     * @param CartRepositoryInterface $cartRepository
     * @param Session $checkoutSession
     * @param GetQuoteDataInterface $getQuoteData
     * @param SaveQuoteDataInterface $saveQuoteData
     * @param LoggerInterface $logger
     */
    public function __construct(
        Generic $paypalSession,
        CartRepositoryInterface $cartRepository,
        Session $checkoutSession,
        GetQuoteDataInterface $getQuoteData,
        SaveQuoteDataInterface $saveQuoteData,
        LoggerInterface $logger
    ) {
        $this->paypalSession = $paypalSession;
        $this->quoteRepository = $cartRepository;
        $this->checkoutSession = $checkoutSession;
        $this->getQuoteData = $getQuoteData;
        $this->saveQuoteData = $saveQuoteData;
        $this->logger = $logger;
    }

    /**
     * @param SaveShippingMethod $subject
     * @return void
     */
    public function beforeExecute(SaveShippingMethod $subject): void
    {
        $origins = $subject->getRequest()->getParam('origin');
        $newShippingMethod = $subject->getRequest()->getParam('shipping_method');
        try {
            $quote = $this->getQuote($subject);
        } catch (NoSuchEntityException|LocalizedException $e) {
            $this->logger->error($e->getMessage());
            return;
        }

        $quoteData = $this->getQuoteData->get($quote->getId());

        if ($origins && $quoteData) {
            $splitShipment = [];
            $shippingAddress = $quote->getShippingAddress();
            $savedMethod = $shippingAddress->getShippingMethod();
            if (strtolower($newShippingMethod ?? '') === 'calcurates_metarate') {
                foreach ($origins as $id => $method) {
                    $splitShipment[] = [
                        'origin' => $id,
                        'method' => $method
                    ];
                }
            }
            $quoteData->setSplitShipments($splitShipment);
            $this->saveQuoteData->save($quoteData);

            if (strtolower($savedMethod ?? '') === 'calcurates_metarate') {
                $shippingAddress->setShippingMethod('');
                $this->quoteRepository->save($quote);
            }
        }
    }

    /**
     * @param SaveShippingMethod $subject
     * @return Quote|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getQuote(SaveShippingMethod $subject): ?Quote
    {
        if (!$this->quote) {
            if ($this->paypalSession->getQuoteId()) {
                $this->quote = $this->quoteRepository->get($this->paypalSession->getQuoteId());
                $this->checkoutSession->replaceQuote($this->quote);
            } else {
                $this->quote = $this->checkoutSession->getQuote();
            }
        }
        return $this->quote;
    }
}
