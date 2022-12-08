<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Plugin;

use Calcurates\ModuleMagento\Model\Config;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Page\Config\Renderer;

class PageConfigRenderer
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var mixed
     */
    private $amastyCheckoutConfig;

    /**
     * @param Config $config
     * @param LayoutInterface $layout
     * @param Manager $moduleManager
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Config $config,
        LayoutInterface $layout,
        Manager $moduleManager,
        ObjectManagerInterface $objectManager
    ) {
        $this->config = $config;
        $this->layout = $layout;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        if ($this->moduleManager->isEnabled('Amasty_CheckoutCore')) {
            $this->amastyCheckoutConfig = $objectManager->get(\Amasty\CheckoutCore\Model\Config::class);
        }
    }

    /**
     * Render 'calcurates_module_enabled' flag before require.js
     * @param Renderer $subject
     * @param string $result
     * @return string
     */
    public function afterRenderTitle(Renderer $subject, string $result): string
    {
        if ($this->config->isActive() && $this->layout->getBlock('calcurates.enabled')) {
            $isAmOscEnabled = $this->amastyCheckoutConfig ? $this->amastyCheckoutConfig->isEnabled() : false;
            $result .= $this->layout->getBlock('calcurates.enabled')
                    ->setData('am_osc_enabled', $isAmOscEnabled)
                    ->toHtml() . "\n";
            $this->layout->unsetElement('calcurates.enabled');
        }
        return $result;
    }
}
