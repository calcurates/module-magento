<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;

class Config implements ConfigProviderInterface
{
    /**
     * Group config key from system.xml
     */
    public const CONFIG_GROUP = 'carriers/calcurates/';

    public const CONFIG_TOKEN = 'calcurates_token';
    public const CONFIG_API_URL = 'api_url';
    public const CONFIG_DISPLAY_RATES_WITH_TAX = 'display_rates_with_tax';
    public const CONFIG_ERROR_MESSAGE = 'specificerrmsg';
    public const CONFIG_TITLE = 'specificerrmsg';
    public const API_GET_RATES_TIMEOUT = 'api_get_rates_timeout';
    public const SHIPPING_METHODS_FOR_FALLBACK = 'shipping_methods_for_fallback';
    public const DELIVERY_DATE_DISPLAY = 'delivery_date_display';
    public const DELIVERY_DATE_DISPLAY_TYPE = 'delivery_date_display_type';
    public const DISPLAY_IMAGES = 'display_shipping_options_images';
    public const DISPLAY_PACKAGE_NAME_FOR_CARRIER = 'display_package_name_for_carrier';
    public const SHIPPING_ON_PRODUCT_ENABLED = 'shipping_on_product_enabled';
    public const SHIPPING_ON_PRODUCT_FALLBACK_MESSAGE = 'shipping_on_product_fallback_message';
    public const STORE_PICKUP_DISPLAY = 'store_pickup_display';

    public const ACTIVE = 'active';
    public const DEBUG = 'debug';

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

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ConfigDataInterfaceFactory $dataFactory,
        Timezone $timezone
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->dataFactory = $dataFactory;
        $this->timezone = $timezone;
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
     * @return int
     */
    public function getRatesTaxDisplayType(): int
    {
        return (int)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::CONFIG_DISPLAY_RATES_WITH_TAX,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return string
     */
    public function getCalcuratesToken()
    {
        return $this->scopeConfig->getValue(self::CONFIG_GROUP . self::CONFIG_TOKEN, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return string
     */
    public function getApiUrl($storeId)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::CONFIG_API_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return \Composer\Package\RootPackageInterface
     */
    public function getComposerPackage()
    {
        static $composerPackage = null;

        if (!$composerPackage) {
            try {
                $composerPackage = (new ComposerFactory())->createComposer(
                    new BufferIO(),
                    __DIR__ . '/../composer.json',
                    true,
                    null,
                    false
                )->getPackage();
            } catch (\Throwable $e) {
                $composerPackage = new DataObject();
                $composerPackage->setName('calcurates/module-magento');
                $composerPackage->setVersion('0.0.0');
            }

        }

        return $composerPackage;
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return string
     */
    public function getTitle($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::CONFIG_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return string
     */
    public function getErrorMessage($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::CONFIG_ERROR_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return int
     */
    public function getApiGetRatesTimeout($storeId)
    {
        return (int)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::API_GET_RATES_TIMEOUT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return array
     */
    public function getShippingMethodsForFallback($storeId)
    {
        $list = $this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::SHIPPING_METHODS_FOR_FALLBACK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $list = explode(',', $list);
        $list = array_filter($list);

        return $list;
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return bool
     */
    public function isActive($storeId)
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_GROUP . self::ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return bool
     */
    public function isDebug($storeId)
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_GROUP . self::DEBUG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string|null $storeId
     * @return string
     */
    public function getDeliveryDateDisplay($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::DELIVERY_DATE_DISPLAY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string|null $storeId
     * @return string
     */
    public function getDeliveryDateDisplayType($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::DELIVERY_DATE_DISPLAY_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return bool
     */
    public function isDisplayImages($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_GROUP . self::DISPLAY_IMAGES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return bool
     */
    public function isDisplayPackageNameForCarrier($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_GROUP . self::DISPLAY_PACKAGE_NAME_FOR_CARRIER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return bool
     */
    public function isShippingOnProductEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_GROUP . self::SHIPPING_ON_PRODUCT_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return string|null
     */
    public function getShippingOnProductFallbackMessage($storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::SHIPPING_ON_PRODUCT_FALLBACK_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string|null $storeId
     * @return string
     */
    public function getStorePickupDisplayAs($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::STORE_PICKUP_DISPLAY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
