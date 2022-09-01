<?php

namespace Calcurates\ModuleMagento\Plugin;

use Calcurates\ModuleMagento\Model\Config;
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
     * @param Config $config
     * @param LayoutInterface $layout
     */
    public function __construct(Config $config, LayoutInterface $layout)
    {
        $this->config = $config;
        $this->layout = $layout;
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
            $result .= $this->layout->getBlock('calcurates.enabled')->toHtml() . "\n";
            $this->layout->unsetElement('calcurates.enabled');
        }
        return $result;
    }
}
