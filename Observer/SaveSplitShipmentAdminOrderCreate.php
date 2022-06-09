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
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\GetQuoteDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\SaveQuoteDataInterface;

class SaveSplitShipmentAdminOrderCreate implements ObserverInterface
{
    /**
     * @var GetQuoteDataInterface
     */
    private $getQuoteData;

    /**
     * @var SaveQuoteDataInterface
     */
    private $saveQuoteData;

    /**
     * @param GetQuoteDataInterface $getQuoteData
     * @param SaveQuoteDataInterface $saveQuoteData
     */
    public function __construct(
        GetQuoteDataInterface $getQuoteData,
        SaveQuoteDataInterface $saveQuoteData
    ) {
        $this->getQuoteData = $getQuoteData;
        $this->saveQuoteData = $saveQuoteData;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $splitShipment = $observer->getRequestModel()->getPost('calcurates_split_shipments');

        if ($splitShipment) {
            $quote = $observer->getOrderCreateModel()->getQuote();
            $quoteData = $this->getQuoteData->get((int)$quote->getId());
            $splitShipmentArray = [];
            foreach ($splitShipment as $originId => $method) {
                $splitShipmentArray[] = [
                    'origin' => $originId,
                    'method' => $method
                ];
            }
            $quoteData->setSplitShipments($splitShipmentArray);
            $this->saveQuoteData->save($quoteData);
        }
    }
}
