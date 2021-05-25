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

    public function isEnabled(): bool
    {
        return $this->config->isShippingOnProductEnabled();
    }

    public function getFallbackMessage(): ?string
    {
        return $this->config->getShippingOnProductFallbackMessage();
    }
}
