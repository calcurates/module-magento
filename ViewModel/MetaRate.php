<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\ViewModel;

use Calcurates\ModuleMagento\Api\Data\MetaRateDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\GetQuoteDataInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Model\Quote\Item;

class MetaRate implements ArgumentInterface
{
    /**
     * @var MetaRateDataInterface
     */
    private $metaRateData;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var GetQuoteDataInterface
     */
    private $getQuoteData;

    /**
     * @var array
     */
    private $savedMethods = [];

    /**
     * @param MetaRateDataInterface $metaRateData
     * @param Session $checkoutSession
     * @param GetQuoteDataInterface $getQuoteData
     */
    public function __construct(
        MetaRateDataInterface $metaRateData,
        Session $checkoutSession,
        GetQuoteDataInterface $getQuoteData
    ) {
        $this->metaRateData = $metaRateData;
        $this->checkoutSession = $checkoutSession;
        $this->getQuoteData = $getQuoteData;
    }

    /**
     * @return MetaRateDataInterface
     */
    public function getMetaRateData(): MetaRateDataInterface
    {
        return $this->metaRateData;
    }

    /**
     * @param int $id
     * @return Item|null
     */
    public function getQuoteItemById(int $id): ?Item
    {
        try {
            return $this->checkoutSession->getQuote()->getItemById($id);
        } catch (NoSuchEntityException|LocalizedException $e) {
            return null;
        }
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getSkusListByItemIds(array $ids): array
    {
        $skus = [];
        foreach ($ids as $id) {
            $item = $this->getQuoteItemById($id);
            if ($item) {
                $skus[] = $item->getSku();
            }
        }
        return $skus;
    }

    /**
     * @param $originId
     * @param $method
     * @return bool
     */
    public function isSavedMethod($originId, $method): bool
    {
        if (!$this->savedMethods) {
            $quoteData = $this->getQuoteData->get(
                $this->checkoutSession->getQuoteId()
            );
            foreach ($quoteData->getSplitShipments() ?? [] as $splitShipment) {
                $this->savedMethods[$splitShipment['origin']] = [
                    'method' => $splitShipment['method']
                ];
            }
        }
        if ($this->savedMethods && isset($this->savedMethods[$originId])) {
            return $this->savedMethods[$originId]['method'] === $method;
        }
        return false;
    }
}
