<?php
/**
 * @author Calcurates Team
 * @copyright Copyright (c) 2019 Calcurates (https://www.calcurates.com)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model;

use Calcurates\ModuleMagento\Api\ConfigProviderInterface;
use Calcurates\ModuleMagento\Api\Data\ConfigDataInterface;
use Calcurates\ModuleMagento\Api\Data\ConfigDataInterfaceFactory;
use Calcurates\ModuleMagento\Model\Config\Data;
use Composer\Factory as ComposerFactory;
use Composer\IO\BufferIO;
use Magento\Directory\Helper\Data as DirectoryData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;

class Config implements ConfigProviderInterface
{
    /**
     * Group config key from system.xml
     */
    const CONFIG_GROUP = 'carriers/calcurates/';

    const CONFIG_TOKEN = 'calcurates_token';

    const CONFIG_API_URL = 'api_url';

    const CONFIG_ERROR_MESSAGE = 'specificerrmsg';

    const CONFIG_ATTRIBUTES_VOLUMETRIC_WEIGHT = 'checkout/attributes/volumetric-weight';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigDataInterfaceFactory
     */
    private $dataFactory;

    /**
     * @var Timezone
     */
    private $timezone;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ConfigDataInterfaceFactory $dataFactory,
        Timezone $timezone,
        SerializerInterface $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->dataFactory = $dataFactory;
        $this->timezone = $timezone;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSettings($websiteId = null)
    {
        /** @var Website $website */
        $website = $this->storeManager->getWebsite($websiteId);

        /** @var ConfigDataInterface|Data $data */
        $data = $this->dataFactory->create();

        $data->setData(
            [
                ConfigDataInterface::BASE_CURRENCY => $website->getBaseCurrencyCode(),
                ConfigDataInterface::WEIGHT_UNIT => $this->scopeConfig->getValue(
                    DirectoryData::XML_PATH_WEIGHT_UNIT,
                    ScopeInterface::SCOPE_WEBSITE,
                    $websiteId
                ),
                ConfigDataInterface::TIMEZONE => $this->timezone->getConfigTimezone(
                    ScopeInterface::SCOPE_WEBSITE,
                    $websiteId
                ),
            ]
        );

        return $data;
    }

    /**
     * @return string
     */
    public function getCalcuratesToken()
    {
        return $this->scopeConfig->getValue(self::CONFIG_GROUP.self::CONFIG_TOKEN);
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return string
     */
    public function getApiUrl($storeId)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_GROUP.self::CONFIG_API_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return array
     */
    public function getLinkedVolumetricWeightAttributes()
    {
        $data = $this->scopeConfig->getValue(
            self::CONFIG_GROUP.self::CONFIG_ATTRIBUTES_VOLUMETRIC_WEIGHT,
            ScopeInterface::SCOPE_WEBSITES
        );

        return $data ? $this->serializer->unserialize($data) : [];
    }

    /**
     * @return \Composer\Package\RootPackageInterface
     */
    public function getComposerPackage()
    {
        static $composerPackage = null;

        if (!$composerPackage) {
            $composerPackage = ComposerFactory::create(
                new BufferIO(),
                __DIR__.'/../composer.json'
            )->getPackage();
        }

        return $composerPackage;
    }
}
