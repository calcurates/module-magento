<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Model\Checkout;

use Calcurates\ModuleMagento\Api\Data\QuoteDataInterfaceFactory;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\GetQuoteDataInterface;
use Calcurates\ModuleMagento\Api\SalesData\QuoteData\SaveQuoteDataInterface;
use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Model\TotalsInformationManagement;

class CalculateTotalsInformation
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
     * @var QuoteDataInterfaceFactory
     */
    private $quoteDataFactory;

    /**
     * @param GetQuoteDataInterface $getQuoteData
     * @param SaveQuoteDataInterface $saveQuoteData
     * @param QuoteDataInterfaceFactory $quoteDataFactory
     */
    public function __construct(
        GetQuoteDataInterface $getQuoteData,
        SaveQuoteDataInterface $saveQuoteData,
        QuoteDataInterfaceFactory $quoteDataFactory
    ) {
        $this->getQuoteData = $getQuoteData;
        $this->saveQuoteData = $saveQuoteData;
        $this->quoteDataFactory = $quoteDataFactory;
    }

    /**
     * @param TotalsInformationManagement $subject
     * @param int $cartId
     * @param TotalsInformationInterface $addressInformation
     * @return array
     */
    public function beforeCalculate(
        TotalsInformationManagement $subject,
        $cartId,
        TotalsInformationInterface  $addressInformation
    ): array {
        $quoteData = $this->getQuoteData->get((int)$cartId);
        if (!$quoteData) {
            $quoteData = $this->quoteDataFactory->create();
        }
        $quoteData->setQuoteId((int)$cartId);
        try {
            $splitShipments = $addressInformation->getExtensionAttributes()->getCalcuratesSplitShipments();
            $splitShipmentArray = [];
            if (!$splitShipments && $addressInformation->getAddress()) {
                $extensionAttributes = $addressInformation->getAddress()
                    ->getExtensionAttributes();
                if (method_exists($extensionAttributes, 'getAdvancedConditions')) {
                    $advancedConditions = $extensionAttributes->getAdvancedConditions();
                }

                if (isset($advancedConditions)) {
                    $splitShipmentArray = $quoteData->getSplitShipments();
                }
            } else {
                foreach ($splitShipments as $splitShipment) {
                    $splitShipmentArray[] = $splitShipment->__toArray();
                }
            }
            $quoteData->setSplitShipments($splitShipmentArray);
        } catch (\Exception $exception) {
            $quoteData->setSplitShipments([]);
        }
        $this->saveQuoteData->save($quoteData);

        return [$cartId, $addressInformation];
    }
}
