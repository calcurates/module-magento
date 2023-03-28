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
use Calcurates\ModuleMagento\Model\Measure;

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
                $customPackageData['dimensionsUnit'] = !empty($customPackageData['dimensionsUnit'])
                    ? $this->mapDimensionsUnit($customPackageData['dimensionsUnit'])
                    : null;
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
                $weightUnit = Measure::pound();
                break;
            case 'g':
                $weightUnit = Measure::gram();
                break;
            case 'kg':
                $weightUnit = Measure::kilogram();
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
                $dimensionsUnit = Measure::inch();
                break;
            case 'cm':
                $dimensionsUnit = Measure::centimeter();
                break;
            default:
                throw new InvalidArgumentException('Invalid dimensions units');
        }

        return $dimensionsUnit;
    }
}
