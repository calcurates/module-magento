<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin\Model\Shipping;

use Magento\Shipping\Model\Rate\CarrierResult;
use Calcurates\ModuleMagento\Api\Data\MetaRateDataInterface;

class CarrierResultPlugin
{
    /**
     * @var MetaRateDataInterface
     */
    private $metarateData;

    /**
     * @param MetaRateDataInterface $metaRateData
     */
    public function __construct(
        MetaRateDataInterface $metaRateData
    ) {
        $this->metarateData = $metaRateData;
    }
    /**
     * @param CarrierResult $subject
     * @param array $result
     * @return array
     */
    public function afterGetAllRates(CarrierResult $subject, array $result)
    {
        if (!is_array($result) || !count($result)) {
            return $result;
        }
        foreach ($this->metarateData->getRatesData() ?? [] as $origin => $rates) {
            $this->metarateData->setRatesData($origin, $this->sortRates($rates));
        }
        return $this->sortRates($result);
    }

    /**
     * @see SAAS-1244
     */
    private function sortRates(array $rates): array
    {
        usort($rates, static function (
            \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult $a,
            \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult $b
        ): int {
            if ($a->getData('priority') === $b->getData('priority')) {
                return $a->getData('price') <=> $b->getData('price');
            }
            if (null === $a->getData('priority')) {
                return 1;
            }
            if (null === $b->getData('priority')) {
                return -1;
            }

            return $a->getData('priority') <=> $b->getData('priority');
        });

        return $rates;
    }
}
