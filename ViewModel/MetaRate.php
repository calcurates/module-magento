<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\ViewModel;

use Calcurates\ModuleMagento\Api\Data\MetaRateDataInterface;
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
     * @param MetaRateDataInterface $metaRateData
     * @param Session $checkoutSession
     */
    public function __construct(
        MetaRateDataInterface $metaRateData,
        Session               $checkoutSession
    ) {
        $this->metaRateData = $metaRateData;
        $this->checkoutSession = $checkoutSession;
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
}
