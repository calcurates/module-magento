<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\ViewModel;

use Calcurates\ModuleMagento\Model\Config;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class Estimation implements ArgumentInterface
{
    /**
     * JS layout configuration
     *
     * @var array
     */
    protected $jsLayout = [];

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var array
     */
    private $variableToTemplateMap = [
        '{{hours}}' => '<%= hours %>',
        '{{minutes}}' => '<%= minutes %>',
        '{{seconds}}' => '<%= seconds %>',
    ];

    /**
     * @param Config $configProvider
     * @param HttpContext $httpContext
     * @param StoreManagerInterface $storeManager
     * @param SerializerInterface $serializer
     * @param RequestInterface $request
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Config $configProvider,
        HttpContext $httpContext,
        StoreManagerInterface $storeManager,
        SerializerInterface $serializer,
        RequestInterface $request,
        ProductRepositoryInterface $productRepository
    ) {
        $this->configProvider = $configProvider;
        $this->httpContext = $httpContext;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        $this->request = $request;
        $this->productRepository = $productRepository;
    }

    /**
     * @return int
     */
    public function isEnabled(): int
    {
        return $this->configProvider->isShippingOnProductEnabled();
    }

    /**
     * @return bool|string
     * @throws NoSuchEntityException
     */
    public function getJsLayout(string $jsLayout = null): string
    {
        $jsLayout = $jsLayout ? $this->serializer->unserialize($jsLayout) : [];
        $isLoggedIn = $this->httpContext->getValue(Context::CONTEXT_AUTH);
        $jsLayout['components']['calcurates_rates']['storeCode'] = $this->storeManager->getStore()->getCode();
        $jsLayout['components']['calcurates_rates']['isLoggedIn'] = $isLoggedIn;
        $jsLayout['components']['calcurates_rates']['productId'] = $this->getProductId();
        $jsLayout['components']['calcurates_rates']['fallbackMessage'] =
            $this->configProvider->getShippingOnProductFallbackMessage();
        $jsLayout['components']['calcurates_rates']['googlePlacesApiKey'] =
            $this->configProvider->getGooglePlacesApiKey();
        $jsLayout['components']['calcurates_rates']['googlePlacesEnabled'] =
            $this->configProvider->isGoogleAddressAutocompleteEnabled();
        $jsLayout['components']['calcurates_rates']['googlePlacesInputTitle'] =
            $this->configProvider->getGooglePlacesInputTitle();
        $jsLayout['components']['calcurates_rates']['googlePlacesInputPlaceholder'] =
            $this->configProvider->getGooglePlacesInputPlaceholder();
        $jsLayout['components']['calcurates_rates']['timeTmplString'] =
            $this->processCustomTemplate($this->configProvider->getCountDownTimerFormat());

        return $this->serializer->serialize($jsLayout);
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return (int)$this->request->getParam('id');
    }

    /**
     * @return ProductInterface|null
     */
    public function getProduct()
    {
        try {
            return $this->productRepository->getById($this->getProductId());
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @return bool
     */
    public function isAttrMatch(): bool
    {
        $product = $this->getProduct();

        $attributeCode = $this->configProvider->getProductShippingAttributeCode();
        if (!$attributeCode || !$product) {
            return false;
        }

        $attributeValue = $this->configProvider->getProductShippingAttributeValue();

        if (is_array($product->getAttributeText($attributeCode))) {
            return in_array($attributeValue, $product->getAttributeText($attributeCode));
        } else {
            return (string)$product->getAttributeText($attributeCode) === $attributeValue;
        }
    }

    /**
     * @param string $template
     * @return string
     */
    private function processCustomTemplate(string $template = ''): string
    {
        foreach ($this->variableToTemplateMap as $magentoVariable => $templateString) {
            $template = str_replace($magentoVariable, $templateString, $template);
        }
        return $template;
    }
}
