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
use Magento\Directory\Helper\Data as DirectoryData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;
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
    public const MISSING_ADDRESS_MESSAGE = 'missing_address_message';
    public const CONFIG_TITLE = 'title';
    public const API_GET_RATES_TIMEOUT = 'api_get_rates_timeout';
    public const SHIPPING_METHODS_FOR_FALLBACK = 'shipping_methods_for_fallback';
    public const DELIVERY_DATE_DEFAULT_VALUE_TYPE = 'delivery_date_default_value_type';
    public const DELIVERY_DATE_DISPLAY = 'delivery_date_display';
    public const DELIVERY_DATE_DISPLAY_TYPE = 'delivery_date_display_type';
    public const DELIVERY_DATE_DISPLAY_FORMAT = 'date_format';
    public const INFO_MESSAGE_DISPLAY_POSITION = 'info_message_display_position';
    public const DISPLAY_IMAGES = 'display_shipping_options_images';
    public const DISPLAY_PACKAGE_NAME_FOR_CARRIER = 'display_package_name_for_carrier';
    public const DISPLAY_SHIPPING_OPTION_NAME = 'display_shipping_option_name';
    public const SHIPPING_ON_PRODUCT_ENABLED = 'shipping_on_product_enabled';
    public const SHIPPING_ON_PRODUCT_FALLBACK_MESSAGE = 'shipping_on_product_fallback_message';
    public const STORE_PICKUP_DISPLAY = 'store_pickup_display';
    public const SPLIT_CHECKOUT_ENABLED = 'split_checkout';
    public const SPLIT_CHECKOUT_TITLE = 'split_checkout_title';
    public const ENABLE_GOOGLE_ADDRESS_AUTOCOMPLETE = 'enable_google_address_autocomplete';
    public const GOOGLE_PLACES_API_KEY = 'google_places_api_key';
    public const GOOGLE_PLACES_INPUT_TITLE = 'google_places_input_title';
    public const GOOGLE_PLACES_INPUT_PLACEHOLDER = 'google_places_input_placeholder';
    public const SHIPPING_ON_PRODUCT_ATTRIBUTE_CODE = 'shipping_on_product_attribute_code';
    public const SHIPPING_ON_PRODUCT_ATTRIBUTE_VALUE = 'shipping_on_product_attribute_value';
    public const SHIPPING_ON_PRODUCT_COUNTDOWN_TIMER_FORMAT = 'shipping_on_product_timer_format';
    public const ALLOW_PARTIAL_ADDRESS_REQUESTS = 'allow_partial_address_requests';
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

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ConfigDataInterfaceFactory $dataFactory,
        Timezone $timezone,
        Filesystem $filesystem
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->dataFactory = $dataFactory;
        $this->timezone = $timezone;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSettings($websiteId = null): ConfigDataInterface
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
     * @param int $storeId
     * @return string
     */
    public function getCalcuratesToken(int $storeId): string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::CONFIG_TOKEN,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::CONFIG_API_URL
        );
    }

    /**
     * @return array{name: string, version: string}
     */
    public function getComposerData(): array
    {
        static $data = [];

        if (!$data) {
            try {
                $json = $this->filesystem->getDirectoryReadByPath(__DIR__ . '/..')->readFile('composer.json');
                $data = \Laminas\Json\Json::decode($json, 1);
            } catch (\Throwable $e) {
                $data = [
                    'name' => 'calcurates/module-magento',
                    'version' => '0.0.0',
                ];
            }
        }

        return $data;
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return string
     */
    public function getTitle($storeId): string
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
    public function getErrorMessage($storeId): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::CONFIG_ERROR_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return string
     */
    public function getMissingAddressMessage($storeId): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::MISSING_ADDRESS_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|string $storeId
     * @return int
     */
    public function getApiGetRatesTimeout($storeId): int
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
    public function getShippingMethodsForFallback($storeId): array
    {
        $list = $this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::SHIPPING_METHODS_FOR_FALLBACK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $list = explode(',', $list ?? '');
        return array_filter($list);
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string|null $storeId
     * @return bool
     */
    public function isActive($storeId = null): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(
            self::CONFIG_GROUP . self::ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return bool
     */
    public function isDebug($storeId): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(
            self::CONFIG_GROUP . self::DEBUG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return bool
     */
    public function isAllowPartialAddressRequests($storeId): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(
            self::CONFIG_GROUP . self::ALLOW_PARTIAL_ADDRESS_REQUESTS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string|null $storeId
     * @return string
     */
    public function getDeliveryDateDefaultValueType($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::DELIVERY_DATE_DEFAULT_VALUE_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string|null $storeId
     * @return string
     */
    public function getDeliveryDateDisplay($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::DELIVERY_DATE_DISPLAY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string|null $storeId
     * @return string
     */
    public function getDeliveryDateDisplayType($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::DELIVERY_DATE_DISPLAY_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string|null $storeId
     * @return string
     */
    public function getDeliveryDateDisplayFormat($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::DELIVERY_DATE_DISPLAY_FORMAT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return bool
     */
    public function isDisplayImages($storeId = null): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(
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
        return (bool)$this->scopeConfig->isSetFlag(
            self::CONFIG_GROUP . self::DISPLAY_PACKAGE_NAME_FOR_CARRIER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return bool
     */
    public function isDisplayShippingOptionName($storeId = null): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(
            self::CONFIG_GROUP . self::DISPLAY_SHIPPING_OPTION_NAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return int
     */
    public function isShippingOnProductEnabled($storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(
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

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isSplitCheckoutEnabled(int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(
            self::CONFIG_GROUP . self::SPLIT_CHECKOUT_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getSplitCheckoutTitle(int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::SPLIT_CHECKOUT_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getCarrierTitle(int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . 'title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string $storeId
     * @return bool
     */
    public function isGoogleAddressAutocompleteEnabled($storeId = null): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(
            self::CONFIG_GROUP . self::ENABLE_GOOGLE_ADDRESS_AUTOCOMPLETE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string|null $storeId
     * @return string
     */
    public function getGooglePlacesApiKey($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::GOOGLE_PLACES_API_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string|null $storeId
     * @return string
     */
    public function getGooglePlacesInputTitle($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::GOOGLE_PLACES_INPUT_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string|null $storeId
     * @return string
     */
    public function getGooglePlacesInputPlaceholder($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::GOOGLE_PLACES_INPUT_PLACEHOLDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string|null $storeId
     * @return string
     */
    public function getProductShippingAttributeCode($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::SHIPPING_ON_PRODUCT_ATTRIBUTE_CODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string|null $storeId
     * @return string
     */
    public function getCountDownTimerFormat($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::SHIPPING_ON_PRODUCT_COUNTDOWN_TIMER_FORMAT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string|null $storeId
     * @return string
     */
    public function getProductShippingAttributeValue($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::SHIPPING_ON_PRODUCT_ATTRIBUTE_VALUE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\App\ScopeInterface|int|string|null $storeId
     * @return string
     */
    public function getInfoMessagePosition($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_GROUP . self::INFO_MESSAGE_DISPLAY_POSITION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
