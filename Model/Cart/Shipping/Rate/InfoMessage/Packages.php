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
                $grouped = [];
                $this->processRatePackageGrouped($grouped, $packagesForCurrentRate);
                $groupedBySource[] = $grouped;
            }
        }
        if ($rates = $rateModel->getRates()) {
            $grouped = [];
            foreach ($rates as $rate) {
                $this->processRatePackageGrouped($grouped, $rate['packages'] ?? []);
            }
            $groupedBySource[] = $grouped;
        }
        if ($packages = $rateModel->getPackages()) {
            $grouped = [];
            $this->processRatePackageGrouped($grouped, $packages);
            $groupedBySource[] = $grouped;
        }

        $ratePackageGrouped = [];
        foreach ($groupedBySource ?? [] as $grouped) {
            foreach ($grouped as $key => $packageInfo) {
                if (!isset($ratePackageGrouped[$key])
                    || $ratePackageGrouped[$key]['qty'] < $packageInfo['qty']
                ) {
                    $ratePackageGrouped[$key] = $packageInfo;
                }
            }
        }

        if (!empty($ratePackageGrouped)) {
            $parts = [];
            foreach ($ratePackageGrouped as $packageInfo) {
                $parts[] = $packageInfo['name'] . ' x' . $packageInfo['qty'];
            }
            $replace = implode('; ', $parts);

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
        if (!is_array($ratePackageGrouped)) {
            $ratePackageGrouped = [];
        }

        foreach ($packages as $package) {
            $key = $package['customPackageId'] ?? ($package['code'] ?? ($package['name'] ?? ''));

            if (isset($ratePackageGrouped[$key])) {
                $ratePackageGrouped[$key]['qty']++;
            } else {
                $ratePackageGrouped[$key] = [
                    'qty' => 1,
                    'name' => $package['name'] ?? ($package['code'] ?? '')
                ];
            }
        }
    }
}
