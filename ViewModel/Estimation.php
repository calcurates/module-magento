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
     * @param Config $configProvider
     * @param HttpContext $httpContext
     * @param StoreManagerInterface $storeManager
     * @param SerializerInterface $serializer
     * @param RequestInterface $request
     */
    public function __construct(
        Config $configProvider,
        HttpContext $httpContext,
        StoreManagerInterface $storeManager,
        SerializerInterface $serializer,
        RequestInterface $request
    ) {
        $this->configProvider = $configProvider;
        $this->httpContext = $httpContext;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        $this->request = $request;
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
        $jsLayout['components']['calcurates_rates']['isEnabled'] = $this->isEnabled();
        $jsLayout['components']['calcurates_rates']['fallbackMessage'] =
            $this->configProvider->getShippingOnProductFallbackMessage();
        $jsLayout['components']['calcurates_rates']['googlePlacesApiKey'] =
            $this->configProvider->getGooglePlacesApiKey();
        $jsLayout['components']['calcurates_rates']['googlePlacesEnabled'] =
            $this->configProvider->isGoogleAddressAutocompleteEnabled();
        $jsLayout['components']['calcurates_rates']['googlePlacesInputTitle'] =
            $this->configProvider->getGooglePlacesInputTitle();

        return $this->serializer->serialize($jsLayout);
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return (int)$this->request->getParam('id');
    }
}
