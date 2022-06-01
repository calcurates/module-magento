<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Shipment;

use Calcurates\ModuleMagento\Api\Client\CalcuratesClientInterface;
use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Shipment;
use Zend_Measure_Length;
use Zend_Measure_Weight;

class CustomPackagesProvider
{
    /**
     * @var CalcuratesClientInterface
     */
    private $calcuratesClient;

    /**
     * @var array|null
     */
    private $packages;

    /**
     * @var CarrierPackagesRetriever
     */
    private $carrierPackagesRetriever;

    public function __construct(
        CalcuratesClientInterface $calcuratesClient,
        CarrierPackagesRetriever $carrierPackagesRetriever
    ) {
        $this->calcuratesClient = $calcuratesClient;
        $this->carrierPackagesRetriever = $carrierPackagesRetriever;
    }

    /**
     * @param Shipment $shipment
     * @return array
     * @throws LocalizedException
     */
    public function getCustomPackages(Shipment $shipment): array
    {
        if ($this->packages === null) {
            $packages = $this->calcuratesClient->getCustomPackages($shipment->getStoreId());

            $carrierPackages = $this->carrierPackagesRetriever->retrievePackages($shipment->getOrder());
            $packages = $this->appendNotExists($packages, $carrierPackages);

            foreach ($packages as &$customPackageData) {
                $customPackageData['weightUnit'] = $this->mapWeightUnit($customPackageData['weightUnit']);
                $customPackageData['dimensionsUnit'] = $this->mapDimensionsUnit($customPackageData['dimensionsUnit']);
            }
            unset($customPackageData);

            $this->packages = $packages;
        }

        return $this->packages;
    }

    private function appendNotExists(array $packages, array $packagesToAppend): array
    {
        $map = [];
        foreach ($packages as $package) {
            $map[$package['id']] = true;
        }

        foreach ($packagesToAppend as $package) {
            if (!isset($map[$package['id']])) {
                $packages[] = $package;
            }
        }

        return $packages;
    }

    private function mapWeightUnit(string $weightUnit): string
    {
        switch ($weightUnit) {
            case 'lb':
                $weightUnit = Zend_Measure_Weight::POUND;
                break;
            case 'g':
                $weightUnit = Zend_Measure_Weight::GRAM;
                break;
            case 'kg':
                $weightUnit = Zend_Measure_Weight::KILOGRAM;
                break;
            default:
                throw new InvalidArgumentException('Invalid weight units');
        }

        return $weightUnit;
    }

    private function mapDimensionsUnit(string $dimensionsUnit): string
    {
        switch ($dimensionsUnit) {
            case 'in':
                $dimensionsUnit = Zend_Measure_Length::INCH;
                break;
            case 'cm':
                $dimensionsUnit = Zend_Measure_Length::CENTIMETER;
                break;
            default:
                throw new InvalidArgumentException('Invalid dimensions units');
        }

        return $dimensionsUnit;
    }
}
