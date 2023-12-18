<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\InfoMessage;

use Calcurates\ModuleMagento\Model\Cart\Shipping\Rate\OutputProcessorInterface;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Framework\DataObject;

class Packages implements OutputProcessorInterface
{
    /**
     * @var string
     */
    private $variableTemplate = '{packages}';

    /**
     * @param Rate|Method|Error|DataObject $rateModel
     * @param string $stringToProcess
     * @return string
     */
    public function process(DataObject $rateModel, string $stringToProcess): string
    {
        if (false === \strpos($stringToProcess, $this->variableTemplate)) {
            return $stringToProcess;
        }
        if ($rateModel->getAddress()) {
            $packages = $rateModel->getAddress()->getQuote()->getCalcuratesCarrierPackages();
        }
        $replace  = '';
        if (isset($packages) && $packages) {
            $packages = json_decode($packages, true);
            $carrierPackages = json_decode(
                $rateModel->getAddress()->getQuote()->getCalcuratesCarrierSrvsSrsCodes(),
                true
            );
            $methodName = $rateModel->getMethod();
            $packageIdsString = '';
            $serviceMethodId = '';
            foreach ($carrierPackages as $serviceId => $packageConfig) {
                $methodNameParts = explode($serviceId, $methodName);
                if (count($methodNameParts) > 1 && isset($methodNameParts[1]) && $methodNameParts[1]) {
                    $packageIdsString = ltrim($methodNameParts[1], '_');
                    $serviceMethodId = $serviceId;
                    break;
                }
            }

            if ($packageIdsString && isset($packages[$serviceMethodId][$packageIdsString])) {
                $packagesForCurrentRate = $packages[$serviceMethodId][$packageIdsString];
                $ratePackageGrouped = [];
                $this->processRatePackageGrouped($ratePackageGrouped, $packagesForCurrentRate);
            }
        }
        if ($rates = $rateModel->getRates()) {
            foreach ($rates as $rate) {
                $this->processRatePackageGrouped($ratePackageGrouped, $rate['packages'] ?? []);
            }
        }
        if ($packages = $rateModel->getPackages()) {
            $this->processRatePackageGrouped($ratePackageGrouped, $packages);
        }
        if (isset($ratePackageGrouped) && $ratePackageGrouped) {
            foreach ($ratePackageGrouped as $packageCode => $packageInfo) {
                $replace .= $packageInfo['name'];
                $replace .= ' x';
                $replace .= $packageInfo['qty'];
                if (next($ratePackageGrouped) == true) {
                    $replace .= '; ';
                }
            }
            return str_replace(
                $this->variableTemplate,
                $replace,
                $stringToProcess
            );
        }

        return $stringToProcess;
    }

    /**
     * @param $ratePackageGrouped
     * @param array $packages
     */
    private function processRatePackageGrouped(&$ratePackageGrouped, $packages = [])
    {
        foreach ($packages as $package) {
            if (isset($ratePackageGrouped[$package['code']])) {
                $ratePackageGrouped[$package['code']]['qty']++;
            } else {
                $ratePackageGrouped[$package['code']] = [
                    'qty' => 1,
                    'name' => $package['name']
                ];
            }
        }
    }
}
