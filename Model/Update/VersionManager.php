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
        $composerData = $this->config->getComposerData();

        return $composerData['version'];
    }

    /**
     * @return string
     */
    public function getLatestVersion()
    {
        if (null === $this->latestVersion) {
            $version = '';
            $composerData = $this->config->getComposerData();
            $packageData = $this->packageInfoLoader->getPackageData($composerData['name']);
            if (!empty($packageData['package']['versions']) && is_array($packageData['package']['versions'])) {
                foreach ($packageData['package']['versions'] as $versionName => $versionData) {
                    if ($versionName === 'dev-master') {
                        continue;
                    }

                    if (null === $version || version_compare($version, $versionData['version'], '<')) {
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
    public function isVersionLatest(): bool
    {
        $currentVersion = $this->getCurrentVersion();
        $latestVersion = $this->getLatestVersion();

        if (!$latestVersion) {
            return true;
        }

        return version_compare($currentVersion, $latestVersion, '>=');
    }
}
