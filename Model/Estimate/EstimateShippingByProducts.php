<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Estimate;

use Calcurates\ModuleMagento\Api\Client\CalcuratesClientInterface;
use Calcurates\ModuleMagento\Api\Data\SimpleRateInterfaceFactory;
use Calcurates\ModuleMagento\Api\EstimateShippingByProductsInterface;
use Calcurates\ModuleMagento\Client\Http\ApiException;
use Calcurates\ModuleMagento\Client\Request\ProductRateRequestBuilder;
use Calcurates\ModuleMagento\Model\Config;
use Calcurates\ModuleMagento\Model\Config\Source\RateTaxDisplaySource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class EstimateShippingByProducts implements EstimateShippingByProductsInterface
{
    /**
     * @var ProductRateRequestBuilder
     */
    private $productRateRequestBuilder;

    /**
     * @var CalcuratesClientInterface
     */
    private $calcuratesClient;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $calcuratesConfig;

    /**
     * @var SimpleRateBuilder
     */
    private $simpleRateBuilder;

    public function __construct(
        ProductRateRequestBuilder $productRateRequestBuilder,
        CalcuratesClientInterface $calcuratesClient,
        StoreManagerInterface $storeManager,
        Config $calcuratesConfig,
        SimpleRateBuilder $simpleRateBuilder
    ) {
        $this->productRateRequestBuilder = $productRateRequestBuilder;
        $this->calcuratesClient = $calcuratesClient;
        $this->storeManager = $storeManager;
        $this->calcuratesConfig = $calcuratesConfig;
        $this->simpleRateBuilder = $simpleRateBuilder;
    }

    /**
     * @param int[] $productIds
     * @param int $customerId
     * @param int|null $storeId
     * @return \Calcurates\ModuleMagento\Api\Data\SimpleRateInterface[]
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function estimate(array $productIds, int $customerId, ?int $storeId = null): array
    {
        $storeId = (int)$this->storeManager->getStore($storeId)->getId();
        $request = $this->productRateRequestBuilder->build($productIds, $customerId, $storeId);
        try {
            $ratesData = $this->calcuratesClient->getRatesSimple($request, $storeId);
        } catch (ApiException $e) {
            throw new LocalizedException(__('Something went wrong with Calcurates API'), $e);
        }

        $displayRatesType = $this->calcuratesConfig->getRatesTaxDisplayType();
        $isDisplayBoth = $displayRatesType === RateTaxDisplaySource::BOTH;
        $isDisplayTaxIncluded = $displayRatesType === RateTaxDisplaySource::TAX_INCLUDED;

        $rates = [];
        foreach ($ratesData as $rateData) {
            if ($isDisplayBoth && $rateData['tax']) {
                $rateDataWithTax = $rateData;
                $rateDataWithTax['cost'] += $rateData['tax'];
                $rateDataWithTax['name'] .= __(' - duties & tax included');

                $rates[] = $this->simpleRateBuilder->build($rateDataWithTax);
            }

            if ($isDisplayTaxIncluded && $rateData['tax']) {
                $rateData['cost'] += $rateData['tax'];
                $rateData['name'] .= __(' - duties & tax included');
            }
            $rates[] = $this->simpleRateBuilder->build($rateData);
        }

        return $rates;
    }
}
