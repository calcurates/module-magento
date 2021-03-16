<?php

declare(strict_types=1);

namespace Calcurates\ModuleMagento\ViewModel;

use Calcurates\ModuleMagento\Model\Config;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Estimation implements ArgumentInterface
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function isEnabled():bool
    {
        return $this->config->isShippingOnProductEnabled();
    }

    /**
     * @return string|null
     */
    public function getFallbackMessage(): ?string
    {
        return $this->config->getShippingOnProductFallbackMessage();
    }
}
