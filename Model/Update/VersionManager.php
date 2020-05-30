<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Update;

use Calcurates\ModuleMagento\Model\Config;

class VersionManager
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var PackageInfoLoader
     */
    private $packageInfoLoader;

    /**
     * @var string|null
     */
    private $latestVersion;

    /**
     * VersionChecker constructor.
     * @param Config $config
     * @param PackageInfoLoader $packageInfoLoader
     */
    public function __construct(Config $config, PackageInfoLoader $packageInfoLoader)
    {
        $this->config = $config;
        $this->packageInfoLoader = $packageInfoLoader;
    }

    /**
     * @return string
     */
    public function getCurrentVersion()
    {
        $composerPackage = $this->config->getComposerPackage();

        return $composerPackage->getPrettyVersion();
    }

    /**
     * @return string
     */
    public function getLatestVersion()
    {
        if (is_null($this->latestVersion)) {
            $version = '';
            $composerPackage = $this->config->getComposerPackage();
            $packageData = $this->packageInfoLoader->getPackageData($composerPackage->getName());
            if (!empty($packageData['package']['versions']) && is_array($packageData['package']['versions'])) {
                foreach ($packageData['package']['versions'] as $versionName => $versionData) {
                    if ($versionName === 'dev-master') {
                        continue;
                    }

                    if (is_null($version) || version_compare($version, $versionData['version'],'<')) {
                        $version = $versionData['version'];
                    }
                }
            }

            $this->latestVersion = $version;
        }

        return $this->latestVersion;
    }

    /**
     * @return bool
     */
    public function isVersionLatest()
    {
        $currentVersion = $this->getCurrentVersion();
        $latestVersion = $this->getLatestVersion();

        if (!$latestVersion) {
            return true;
        }

        return version_compare($currentVersion, $latestVersion, '>=');
    }
}
